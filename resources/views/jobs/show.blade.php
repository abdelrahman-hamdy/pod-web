@extends('layouts.app')

@section('title', $job->title . ' - Jobs - People Of Data')

@section('content')
    
<div class="flex flex-col lg:flex-row lg:gap-8 gap-6 w-full justify-center">
    <!-- Job Content -->
    <div class="flex-1 w-full lg:max-w-3xl min-w-0 lg:flex-shrink-0">
        <!-- Job Header -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden mb-8">
            <div class="p-8">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h1 class="text-3xl font-bold text-slate-800 mb-2">{{ $job->title }}</h1>
                        
                        @if($job->formatted_salary)
                            <p class="text-2xl font-bold text-green-600 mb-4">
                                EGP {{ number_format($job->salary_min ?? 0) }}@if($job->salary_max) - {{ number_format($job->salary_max) }}@endif
                            </p>
                        @endif

                        <div class="space-y-2 mb-4">
                            <div class="flex items-center space-x-1 text-sm text-slate-600">
                                <i class="ri-map-pin-line text-indigo-600"></i>
                                <span>
                                    @if($job->location_type === \App\LocationType::REMOTE)
                                        <span class="text-indigo-600">Remote</span>
                                    @else
                                        <span class="text-indigo-600">{{ $job->location }}</span>
                                    @endif
                                </span>
                            </div>
                            
                            <div class="flex items-center space-x-1 text-sm text-slate-600">
                                <i class="ri-calendar-line"></i>
                                <span>Posted {{ $job->created_at->diffForHumans() }}</span>
                                <span class="text-slate-400">•</span>
                                <span>Deadline: {{ $job->application_deadline->format('M j, Y') }}</span>
                            </div>
                        </div>

                        <!-- Job Metadata -->
                        <div class="flex flex-wrap gap-2 mb-6">
                            <div class="flex items-center space-x-1.5 text-xs text-slate-600">
                                <div class="flex-shrink-0 w-6 h-6 bg-indigo-50 rounded-md flex items-center justify-center">
                                    <i class="ri-user-line text-indigo-600 text-xs"></i>
                                </div>
                                <span class="font-medium">{{ $job->experience_level->getLabel() }}</span>
                            </div>
                            
                            <div class="flex items-center space-x-1.5 text-xs text-slate-600">
                                <div class="flex-shrink-0 w-6 h-6 bg-purple-50 rounded-md flex items-center justify-center">
                                    <i class="ri-map-pin-line text-purple-600 text-xs"></i>
                                </div>
                                <span class="font-medium">{{ $job->location_type->getLabel() }}</span>
                            </div>
                            
                            @if($job->category)
                            <div class="flex items-center space-x-1.5 text-xs text-slate-600">
                                <div class="flex-shrink-0 w-6 h-6 rounded-md flex items-center justify-center" style="background-color: {{ $job->category->color }}20;">
                                    <i class="ri-folder-line" style="color: {{ $job->category->color }};"></i>
                                </div>
                                <span class="font-medium">{{ $job->category->name }}</span>
                            </div>
                            @endif
                        </div>
                        
                        @if($job->description)
                        <div class="prose prose-slate max-w-none">
                            {!! nl2br(e($job->description)) !!}
                        </div>
                        @endif
                        
                        <!-- Required Skills -->
                        @php
                            $skills = is_array($job->required_skills) 
                                ? $job->required_skills 
                                : (is_string($job->required_skills) ? json_decode($job->required_skills, true) : []);
                        @endphp
                        @if($skills && is_array($skills) && count($skills) > 0)
                        <div class="mt-6 pt-6 border-t border-slate-200">
                            <h3 class="text-lg font-semibold text-slate-800 mb-4">Required Skills</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($skills as $skill)
                                    <span class="inline-flex items-center px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-full text-xs font-medium">
                                        {{ $skill }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    <div class="text-right ml-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            @if($job->status === \App\JobStatus::ACTIVE->value) bg-green-100 text-green-800
                            @elseif($job->status === \App\JobStatus::CLOSED->value) bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ $job->display_status }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Company Description -->
        @if($job->company_description)
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 mb-8">
            <h2 class="text-xl font-semibold text-slate-800 mb-4">About {{ $job->company_name }}</h2>
            <div class="prose prose-slate max-w-none">
                {!! nl2br(e($job->company_description)) !!}
            </div>
        </div>
        @endif

        <!-- Job Poster Profile -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 mb-8">
            <h3 class="text-sm font-medium text-slate-500 mb-3">Posted by</h3>
            <div class="flex items-start space-x-3">
                <a href="{{ route('profile.show.other', $job->poster->id) }}" class="flex-shrink-0">
                    <x-avatar 
                        :src="$job->poster->avatar ?? null"
                        :name="$job->poster->name ?? 'User'"
                        size="sm"
                        :color="$job->poster->avatar_color ?? null" />
                </a>
                <div class="flex-1 min-w-0">
                    <a href="{{ route('profile.show.other', $job->poster->id) }}" class="block">
                        <h3 class="text-base font-semibold text-slate-800 flex items-center">{{ $job->poster->name }}<x-business-badge :user="$job->poster" /></h3>
                    </a>
                    @if($job->poster->bio)
                        <p class="text-sm text-slate-600 mt-1">{{ $job->poster->bio }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Share Job -->
        <x-share-section title="Share This Job">
            <button class="bg-blue-600 text-white px-4 py-3 rounded-button hover:bg-blue-700 transition-colors !rounded-button">
                <i class="ri-facebook-line mr-2"></i>
                Facebook
            </button>
            <button onclick="shareJob('twitter')" class="bg-slate-800 text-white px-4 py-3 rounded-button hover:bg-slate-900 transition-colors !rounded-button">
                <i class="ri-twitter-x-line mr-2"></i>
                X
            </button>
            <button onclick="shareJob('linkedin')" class="bg-blue-700 text-white px-4 py-3 rounded-button hover:bg-blue-800 transition-colors !rounded-button">
                <i class="ri-linkedin-line mr-2"></i>
                LinkedIn
            </button>
            <button onclick="copyJobLink()" class="bg-slate-600 text-white px-4 py-3 rounded-button hover:bg-slate-700 transition-colors !rounded-button">
                <i class="ri-links-line mr-2"></i>
                Copy Link
            </button>
        </x-share-section>
    </div>

    <!-- Right Sidebar -->
    <div class="w-full lg:w-80 lg:flex-shrink-0 min-w-0">
        <div class="space-y-6">
            <!-- Application Widget -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                <h3 class="font-semibold text-slate-800 mb-4">Application</h3>
                
                @if($job->isAcceptingApplications())
                    @if($userApplication)
                        <div class="mb-4">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-3">
                                <div class="flex items-center space-x-2 mb-2">
                                    <i class="ri-check-circle-line text-blue-600"></i>
                                    <span class="text-blue-800 font-medium">You have applied</span>
                                </div>
                                <p class="text-sm text-blue-700">
                                    Application submitted on {{ $userApplication->created_at->format('M j, Y') }}
                                </p>
                            </div>
                            
                            <!-- Application Status -->
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-600">Status:</span>
                                <x-status-badge 
                                    :status="$userApplication->status->value"
                                    :label="$userApplication->status->getLabel()" />
                            </div>
                        </div>
                        
                        @php
                            $userApplication = $job->getUserApplication(Auth::user());
                        @endphp
                        @if($userApplication)
                            <a href="{{ route('jobs.my-application.show', $userApplication) }}" 
                               class="w-full bg-slate-100 text-slate-700 px-4 py-3 rounded-button hover:bg-slate-200 transition-colors !rounded-button text-center block">
                                View Application
                            </a>
                        @endif
                    @else
                        <div class="mb-4">
                            <div class="text-sm text-slate-600 mb-2">Application Deadline</div>
                            <div class="text-lg font-semibold text-slate-800">{{ $job->application_deadline->format('M j, Y') }}</div>
                        </div>
                        
                        <button onclick="showApplicationModal()" 
                                class="w-full bg-indigo-600 text-white px-4 py-3 rounded-button hover:bg-indigo-700 transition-colors !rounded-button">
                            <i class="ri-file-add-line mr-2"></i>
                            Apply Now
                        </button>
                    @endif
                @else
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center space-x-2 mb-2">
                            <i class="ri-close-circle-line text-gray-600"></i>
                            <span class="text-gray-800 font-medium">Applications Closed</span>
                        </div>
                        <p class="text-sm text-gray-600">
                            @if($job->hasDeadlinePassed())
                                The application deadline has passed.
                            @else
                                This position has been closed.
                            @endif
                        </p>
                    </div>
                @endif
            </div>

            <!-- Job Statistics -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                <h3 class="font-semibold text-slate-800 mb-4">Job Statistics</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600">Total Applications</span>
                        <span class="text-sm font-semibold text-indigo-600">{{ $job->applications_count }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600">Experience Level</span>
                        <span class="text-sm font-semibold text-slate-800">{{ $job->experience_level->getLabel() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600">Location Type</span>
                        <span class="text-sm font-semibold text-purple-600">{{ $job->location_type->getLabel() }}</span>
                    </div>
                    @if($job->applications_count > 0)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Posted By</span>
                            <span class="text-sm font-semibold text-slate-800">{{ $job->poster->name }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Applications (for job poster) -->
            @if(auth()->check() && (auth()->id() === $job->posted_by || auth()->user()->hasAnyRole(['superadmin', 'admin'])) && $recentApplications->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-slate-800">Recent Applications</h3>
                        <a href="{{ route('jobs.applications', $job) }}" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                            View All
                        </a>
                    </div>
                    <div class="space-y-3">
                        @foreach($recentApplications as $application)
                            <a href="{{ route('jobs.applications', $job) }}" class="flex items-center space-x-3 hover:bg-slate-50 p-2 -mx-2 rounded-lg transition-colors group">
                                <div class="flex-shrink-0">
                                    <x-avatar 
                                        :src="$application->user->avatar ?? null"
                                        :name="$application->user->name ?? 'User'"
                                        size="sm"
                                        :color="$application->user->avatar_color ?? null" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-800 truncate group-hover:text-indigo-600 transition-colors">{{ $application->user->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $application->created_at->diffForHumans() }}</p>
                                </div>
                                @php
                                    $appStatusValue = $application->status->value;
                                    $appStatusLabel = $application->status->getLabel();
                                    $appStatusClass = match($appStatusValue) {
                                        'pending' => 'bg-yellow-100 text-yellow-700',
                                        'reviewed' => 'bg-blue-100 text-blue-700',
                                        'accepted' => 'bg-green-100 text-green-700',
                                        'rejected' => 'bg-red-100 text-red-700',
                                        default => 'bg-gray-100 text-gray-700',
                                    };
                                @endphp
                                <span class="flex-shrink-0 px-2 py-1 rounded-full text-xs font-medium {{ $appStatusClass }}">
                                    {{ $appStatusLabel }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Job Actions (for job poster) -->
            @if(auth()->check() && (auth()->id() === $job->posted_by || auth()->user()->hasAnyRole(['superadmin', 'admin'])))
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                    <h3 class="font-semibold text-slate-800 mb-4">Job Management</h3>
                    <div class="space-y-3">
                        <a href="{{ route('jobs.edit', $job) }}" 
                           class="w-full bg-indigo-50 text-indigo-700 px-4 py-2 rounded-button hover:bg-indigo-100 transition-colors text-center block !rounded-button">
                            <i class="ri-edit-line mr-2"></i>
                            Edit Job
                        </a>
                        @if($job->status === \App\JobStatus::ACTIVE->value)
                            <button type="button"
                                    onclick="window.openConfirmModal('confirmCloseJob')"
                                    class="w-full bg-orange-50 text-orange-700 px-4 py-2 rounded-button hover:bg-orange-100 transition-colors !rounded-button">
                                <i class="ri-lock-line mr-2"></i>
                                Close Job
                            </button>
                        @elseif($job->status === \App\JobStatus::CLOSED->value)
                            <form action="{{ route('jobs.reopen', $job) }}" method="POST" class="w-full">
                                @csrf
                                @method('PATCH')
                                <button type="submit" 
                                        class="w-full bg-green-50 text-green-700 px-4 py-2 rounded-button hover:bg-green-100 transition-colors !rounded-button">
                                    <i class="ri-lock-unlock-line mr-2"></i>
                                    Reopen Job
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Application Modal -->
<div id="application-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-slate-800">Apply for {{ $job->title }}</h3>
                <button onclick="hideApplicationModal()" class="text-slate-400 hover:text-slate-600">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>
            
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="ri-check-line text-green-600 mr-2"></i>
                        <span class="text-sm text-green-700">{{ session('success') }}</span>
                    </div>
                </div>
                <script>
                    setTimeout(function() {
                        hideApplicationModal();
                        window.location.reload();
                    }, 2000);
                </script>
            @endif
            
            @if($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-start">
                        <i class="ri-error-warning-line text-red-600 mr-2 mt-0.5"></i>
                        <div class="flex-1">
                            <h4 class="text-sm font-medium text-red-800 mb-1">Error submitting application</h4>
                            <ul class="text-sm text-red-700 list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
            
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="ri-error-warning-line text-red-600 mr-2"></i>
                        <span class="text-sm text-red-700">{{ session('error') }}</span>
                    </div>
                </div>
            @endif
            
            <form action="{{ route('jobs.apply', $job) }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Cover Letter *</label>
                        <textarea name="cover_letter" 
                                  rows="4" 
                                  class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('cover_letter') border-red-300 @enderror"
                                  placeholder="Tell us why you're interested in this position..."
                                  required>{{ old('cover_letter') }}</textarea>
                        @error('cover_letter')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Additional Information</label>
                        <textarea name="additional_info" 
                                  rows="3" 
                                  class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                  placeholder="Any additional information you'd like to share...">{{ old('additional_info') }}</textarea>
                    </div>
                </div>
                
                <div class="flex space-x-3 mt-6">
                    <button type="button" 
                            onclick="hideApplicationModal()"
                            class="flex-1 border border-slate-200 text-slate-600 px-4 py-2 rounded-lg hover:bg-slate-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                        Submit Application
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Confirm Close Job Modal -->
<x-confirm-modal
    id="confirmCloseJob"
    title="Close Job Posting"
    message="Are you sure you want to close this job posting? This will prevent new applications."
    confirmText="Close Job"
    cancelText="Cancel"
    :confirmAction="route('jobs.close', $job)"
    confirmMethod="PATCH"
    :danger="true"
/>

@push('scripts')
<script>
function showApplicationModal() {
    document.getElementById('application-modal').classList.remove('hidden');
}

function hideApplicationModal() {
    document.getElementById('application-modal').classList.add('hidden');
}

function shareJob(platform) {
    const url = window.location.href;
    const title = '{{ $job->title }} at {{ $job->company_name }}';
    
    if (platform === 'twitter') {
        window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(title)}&url=${encodeURIComponent(url)}`, '_blank');
    } else if (platform === 'linkedin') {
        window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`, '_blank');
    }
}

function copyJobLink() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        // You could add a toast notification here
        alert('Job link copied to clipboard!');
    });
}

// Confirm modal functions
if (!window.openConfirmModal) {
    window.openConfirmModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'block';
            try {
                // Use Alpine if available
                if (modal.__x && modal.__x.$data) {
                    modal.__x.$data.show = true;
                }
            } catch (e) {
                // Fallback: just show the modal
                const alpineData = modal.querySelector('[x-data]');
                if (alpineData && alpineData.__x && alpineData.__x.$data) {
                    alpineData.__x.$data.show = true;
                }
            }
            document.body.classList.add('overflow-hidden');
        }
    };
}
</script>
@endpush
@endsection