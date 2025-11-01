@extends('layouts.app')

@section('title', 'Applications for ' . $job->title . ' - People Of Data')

@section('content')
<div class="max-w-6xl mx-auto px-6 py-8">
    <!-- Back to Job Link -->
    <div class="mb-6">
        <a href="{{ route('jobs.show', $job) }}" 
           class="flex items-center space-x-2 text-slate-600 hover:text-indigo-600 transition-colors">
            <i class="ri-arrow-left-line"></i>
            <span class="text-sm font-medium">Back to Job</span>
        </a>
    </div>

    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-800 mb-2">Job Applications</h1>
        <p class="text-slate-600">
            <strong>{{ $job->title }}</strong> at {{ $job->company_name }}
        </p>
        <div class="flex items-center space-x-4 mt-2 text-sm text-slate-600">
            <span>{{ $applications->total() }} {{ Str::plural('application', $applications->total()) }}</span>
            <span>•</span>
            <span>Posted {{ $job->created_at->diffForHumans() }}</span>
        </div>
    </div>

    <!-- Applications List -->
    <div class="space-y-6">
        @if($applications->count() > 0)
            @foreach($applications as $application)
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                    <div class="flex items-start justify-between mb-4">
                        <a href="{{ route('profile.show.other', $application->user->id) }}" class="flex items-start space-x-4 hover:opacity-80 transition-opacity">
                            <!-- Applicant Avatar -->
                            <x-avatar 
                                :src="$application->user->avatar ?? null"
                                :name="$application->user->name ?? 'User'"
                                size="md"
                                :color="$application->user->avatar_color ?? null" />
                            
                            <!-- Applicant Info -->
                            <div>
                                <h3 class="text-lg font-semibold text-slate-800 flex items-center">{{ $application->user->name }}<x-business-badge :user="$application->user" /></h3>
                                <p class="text-slate-600">{{ $application->user->email }}</p>
                                <p class="text-sm text-slate-500">Applied {{ $application->created_at->diffForHumans() }}</p>
                            </div>
                        </a>
                        
                        <!-- Application Status -->
                        <div class="flex items-center space-x-3">
                            <x-status-badge 
                                :status="$application->status->value"
                                :label="$application->status->getLabel()" />
                            
                            <!-- Status Actions -->
                            <div class="flex items-center space-x-2">
                                @if($application->status === \App\JobApplicationStatus::PENDING)
                                    <form action="{{ route('jobs.applications.review', $application) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="reviewed">
                                        <button type="submit" 
                                                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium inline-flex items-center space-x-1">
                                            <i class="ri-eye-line"></i>
                                            <span>Mark as Reviewed</span>
                                        </button>
                                    </form>
                                @endif
                                
                                @if($application->status === \App\JobApplicationStatus::REVIEWED)
                                    <div class="flex space-x-2">
                                        <button type="button"
                                                onclick="openConfirmAcceptModal({{ $application->id }})"
                                                class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm font-medium inline-flex items-center space-x-1">
                                            <i class="ri-check-line"></i>
                                            <span>Accept</span>
                                        </button>
                                        <button type="button"
                                                onclick="openConfirmRejectModal({{ $application->id }})"
                                                class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors text-sm font-medium inline-flex items-center space-x-1">
                                            <i class="ri-close-line"></i>
                                            <span>Reject</span>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Application Details -->
                    <div class="space-y-4">
                        <!-- Cover Letter -->
                        <div>
                            <h4 class="font-medium text-slate-800 mb-2">Cover Letter</h4>
                            <div class="bg-slate-50 rounded-lg p-4">
                                <p class="text-slate-700 whitespace-pre-wrap">{{ $application->cover_letter }}</p>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        @if($application->additional_info)
                            <div>
                                <h4 class="font-medium text-slate-800 mb-2">Additional Information</h4>
                                <div class="bg-slate-50 rounded-lg p-4">
                                    <p class="text-slate-700 whitespace-pre-wrap">{{ $application->additional_info }}</p>
                                </div>
                            </div>
                        @endif

                        <!-- Admin Notes -->
                        @if($application->admin_notes)
                            <div>
                                <h4 class="font-medium text-slate-800 mb-2">Admin Notes</h4>
                                <div class="bg-blue-50 rounded-lg p-4">
                                    <p class="text-slate-700 whitespace-pre-wrap">{{ $application->admin_notes }}</p>
                                </div>
                            </div>
                        @endif

                        <!-- Application Actions -->
                        <div class="flex items-center justify-between pt-4 border-t border-slate-200">
                            <div class="flex items-center space-x-4">
                                <button onclick="toggleApplicationDetails({{ $application->id }})" 
                                        class="bg-indigo-50 text-indigo-700 px-4 py-2 rounded-lg hover:bg-indigo-100 transition-colors text-sm font-medium inline-flex items-center space-x-1">
                                    <i class="ri-eye-line"></i>
                                    <span>View Details</span>
                                </button>
                                
                                <button onclick="showNotesModal({{ $application->id }})" 
                                        class="bg-slate-50 text-slate-700 px-4 py-2 rounded-lg hover:bg-slate-100 transition-colors text-sm font-medium inline-flex items-center space-x-1">
                                    <i class="ri-edit-line"></i>
                                    <span>Add Notes</span>
                                </button>
                                
                                <a href="{{ url('/chat/' . $application->user->id) }}" 
                                   class="bg-green-50 text-green-700 px-4 py-2 rounded-lg hover:bg-green-100 transition-colors text-sm font-medium inline-flex items-center space-x-1">
                                    <i class="ri-chat-3-line"></i>
                                    <span>Chat</span>
                                </a>
                            </div>
                            
                            <div class="text-sm text-slate-500">
                                @if($application->status_updated_at)
                                    Status updated {{ $application->status_updated_at->diffForHumans() }}
                                @endif
                            </div>
                        </div>

                        <!-- Expandable Details -->
                        <div id="application-details-{{ $application->id }}" class="hidden">
                            <div class="bg-slate-50 rounded-lg p-4 space-y-3">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <span class="text-sm font-medium text-slate-600">Application ID:</span>
                                        <span class="text-sm text-slate-800">#{{ $application->id }}</span>
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-slate-600">Applied:</span>
                                        <span class="text-sm text-slate-800">{{ $application->created_at->format('M j, Y g:i A') }}</span>
                                    </div>
                                </div>
                                
                                @if($application->user->profile)
                                    <div>
                                        <span class="text-sm font-medium text-slate-600">Profile:</span>
                                        <a href="{{ route('profile.show', $application->user) }}" 
                                           class="text-sm text-indigo-600 hover:text-indigo-800 ml-2">
                                            View Profile
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Pagination -->
            <div class="mt-8">
                {{ $applications->links() }}
            </div>
        @else
            <!-- No Applications -->
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="ri-file-list-line text-slate-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-slate-800 mb-2">No Applications Yet</h3>
                <p class="text-slate-600 mb-6">
                    This job posting hasn't received any applications yet. Consider sharing it to reach more candidates.
                </p>
                <div class="flex items-center justify-center space-x-4">
                    <button onclick="shareJob()" 
                            class="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition-colors">
                        Share Job
                    </button>
                    <a href="{{ route('jobs.edit', $job) }}" 
                       class="bg-slate-600 text-white px-6 py-3 rounded-lg hover:bg-slate-700 transition-colors">
                        Edit Job
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Notes Modal -->
<div id="notes-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-slate-800">Add Admin Notes</h3>
                <button onclick="hideNotesModal()" class="text-slate-400 hover:text-slate-600">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>
            
            <form id="notes-form" method="POST">
                @csrf
                @method('PATCH')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Admin Notes</label>
                        <textarea name="admin_notes" 
                                  rows="4" 
                                  class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                  placeholder="Add your notes about this application..."></textarea>
                    </div>
                </div>
                
                <div class="flex space-x-3 mt-6">
                    <button type="button" 
                            onclick="hideNotesModal()"
                            class="flex-1 border border-slate-200 text-slate-600 px-4 py-2 rounded-lg hover:bg-slate-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                        Save Notes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Confirm Accept Modal -->
<x-confirm-modal
    id="confirmAcceptApplication"
    title="Accept Application"
    message="Are you sure you want to accept this application?"
    confirmText="Accept Application"
    cancelText="Cancel"
    confirmMethod="PATCH"
    :danger="false"
    :success="true"
/>

<!-- Confirm Reject Modal -->
<x-confirm-modal
    id="confirmRejectApplication"
    title="Reject Application"
    message="Are you sure you want to reject this application? This action cannot be undone."
    confirmText="Reject Application"
    cancelText="Cancel"
    confirmMethod="PATCH"
    :danger="true"
/>

@push('scripts')
<script>
    function toggleApplicationDetails(applicationId) {
        const details = document.getElementById(`application-details-${applicationId}`);
        details.classList.toggle('hidden');
    }
    
    function showNotesModal(applicationId) {
        const form = document.getElementById('notes-form');
        form.action = `/jobs/applications/${applicationId}/notes`;
        document.getElementById('notes-modal').classList.remove('hidden');
    }
    
    function hideNotesModal() {
        document.getElementById('notes-modal').classList.add('hidden');
    }
    
    function shareJob() {
        const url = window.location.origin + '{{ route("jobs.show", $job) }}';
        const title = '{{ $job->title }} at {{ $job->company_name }}';
        
        if (navigator.share) {
            navigator.share({
                title: title,
                url: url
            });
        } else {
            navigator.clipboard.writeText(url).then(() => {
                alert('Job link copied to clipboard!');
            });
        }
    }
    
    // Confirm modal functions
    function openConfirmAcceptModal(applicationId) {
        const modal = document.getElementById('confirmAcceptApplication');
        const form = modal.querySelector('form');
        form.action = `/jobs/applications/${applicationId}/accept`;
        
        if (!window.openConfirmModal) {
            window.openConfirmModal = function(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = 'block';
                    try {
                        if (modal.__x && modal.__x.$data) {
                            modal.__x.$data.show = true;
                        }
                    } catch (e) {
                        const alpineData = modal.querySelector('[x-data]');
                        if (alpineData && alpineData.__x && alpineData.__x.$data) {
                            alpineData.__x.$data.show = true;
                        }
                    }
                    document.body.classList.add('overflow-hidden');
                }
            };
        }
        
        window.openConfirmModal('confirmAcceptApplication');
    }
    
    function openConfirmRejectModal(applicationId) {
        const modal = document.getElementById('confirmRejectApplication');
        const form = modal.querySelector('form');
        form.action = `/jobs/applications/${applicationId}/reject`;
        
        if (!window.openConfirmModal) {
            window.openConfirmModal = function(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = 'block';
                    try {
                        if (modal.__x && modal.__x.$data) {
                            modal.__x.$data.show = true;
                        }
                    } catch (e) {
                        const alpineData = modal.querySelector('[x-data]');
                        if (alpineData && alpineData.__x && alpineData.__x.$data) {
                            alpineData.__x.$data.show = true;
                        }
                    }
                    document.body.classList.add('overflow-hidden');
                }
            };
        }
        
        window.openConfirmModal('confirmRejectApplication');
    }
</script>
@endpush
@endsection
