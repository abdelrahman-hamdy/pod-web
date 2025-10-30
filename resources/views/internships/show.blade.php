@extends('layouts.app')

@section('title', $internship->title . ' - Internships - People Of Data')

@section('content')
<div class="flex flex-col lg:flex-row lg:gap-8 gap-6 w-full justify-center">
    <!-- Internship Content -->
    <div class="flex-1 w-full lg:max-w-3xl min-w-0 lg:flex-shrink-0">
        <!-- Internship Header -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden mb-8">
            <!-- Category Badge -->
            @if($internship->category)
            <div class="px-8 pt-6 pb-1">
                <span class="text-xs font-semibold uppercase tracking-wide" style="color: {{ $internship->category->color ?? '#3b82f6' }}">
                    {{ $internship->category->name }}
                </span>
            </div>
            @endif
            
            <div class="p-8 @if(!$internship->category) pt-6 @else pt-2 @endif">
                <div class="flex items-start justify-between mb-6">
                    <div class="flex-1">
                        <div class="flex items-start justify-between gap-4 mb-2">
                            <h1 class="text-3xl font-bold text-slate-800">{{ $internship->title }}</h1>
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium flex-shrink-0
                                @if($internship->status === 'open') bg-green-100 text-green-800
                                @elseif($internship->status === 'closed') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800 @endif">
                                @if($internship->status === 'open')
                                    <i class="ri-checkbox-circle-line mr-1.5"></i>
                                    Open
                                @elseif($internship->status === 'closed')
                                    <i class="ri-close-circle-line mr-1.5"></i>
                                    Closed
                                @else
                                    <i class="ri-file-edit-line mr-1.5"></i>
                                    Draft
                                @endif
                            </span>
                        </div>
                        <div class="flex items-center space-x-2 text-lg text-slate-600">
                            <i class="ri-building-line text-indigo-600"></i>
                            <span class="font-medium">{{ $internship->company_name }}</span>
                        </div>
                    </div>
                </div>

                <!-- Internship Metadata -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-indigo-50 rounded-lg flex items-center justify-center mt-0.5">
                            <i class="ri-map-pin-line text-indigo-600"></i>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500 mb-0.5">Location</div>
                            <div class="font-medium text-slate-800">{{ $internship->location }}</div>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center mt-0.5">
                            <i class="ri-time-line text-purple-600"></i>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500 mb-0.5">Type</div>
                            <div class="font-medium text-slate-800">{{ ucfirst(str_replace('_', ' ', $internship->type)) }}</div>
                        </div>
                    </div>
                    
                    @if($internship->duration)
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center mt-0.5">
                            <i class="ri-calendar-check-line text-green-600"></i>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500 mb-0.5">Duration</div>
                            <div class="font-medium text-slate-800">{{ $internship->duration }}</div>
                        </div>
                    </div>
                    @endif
                    
                    @if($internship->start_date)
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center mt-0.5">
                            <i class="ri-calendar-2-line text-blue-600"></i>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500 mb-0.5">Start Date</div>
                            <div class="font-medium text-slate-800">
                                {{ is_string($internship->start_date) ? \Carbon\Carbon::parse($internship->start_date)->format('M j, Y') : $internship->start_date->format('M j, Y') }}
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-orange-50 rounded-lg flex items-center justify-center mt-0.5">
                            <i class="ri-time-line text-orange-600"></i>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500 mb-0.5">Deadline</div>
                            <div class="font-medium text-slate-800">
                                {{ is_string($internship->application_deadline) ? \Carbon\Carbon::parse($internship->application_deadline)->format('M j, Y') : $internship->application_deadline->format('M j, Y') }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Description -->
                @if($internship->description)
                <div class="prose prose-slate max-w-none border-t border-slate-200 pt-6">
                    {!! nl2br(e($internship->description)) !!}
                </div>
                @endif
            </div>
        </div>

        <!-- Share Internship -->
        <x-share-section title="Share This Internship">
            <button onclick="shareInternship('facebook')" class="bg-blue-600 text-white px-4 py-3 rounded-button hover:bg-blue-700 transition-colors !rounded-button">
                <i class="ri-facebook-line mr-2"></i>
                Facebook
            </button>
            <button onclick="shareInternship('twitter')" class="bg-slate-800 text-white px-4 py-3 rounded-button hover:bg-slate-900 transition-colors !rounded-button">
                <i class="ri-twitter-x-line mr-2"></i>
                X
            </button>
            <button onclick="shareInternship('linkedin')" class="bg-blue-700 text-white px-4 py-3 rounded-button hover:bg-blue-800 transition-colors !rounded-button">
                <i class="ri-linkedin-line mr-2"></i>
                LinkedIn
            </button>
            <button onclick="copyInternshipLink()" class="bg-slate-600 text-white px-4 py-3 rounded-button hover:bg-slate-700 transition-colors !rounded-button">
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
                
                @php
                    $deadlinePassed = is_string($internship->application_deadline) 
                        ? \Carbon\Carbon::parse($internship->application_deadline)->isPast() 
                        : $internship->application_deadline->isPast();
                @endphp
                
                <div class="mb-4">
                    <div class="text-sm text-slate-600 mb-2">Application Deadline</div>
                    <div class="text-lg font-semibold text-slate-800">
                        {{ is_string($internship->application_deadline) ? \Carbon\Carbon::parse($internship->application_deadline)->format('M j, Y') : $internship->application_deadline->format('M j, Y') }}
                    </div>
                </div>
                
                @if(!$deadlinePassed)
                    <a href="{{ route('internships.apply', ['internship_id' => $internship->id]) }}" 
                       class="w-full bg-indigo-600 text-white px-4 py-3 rounded-button hover:bg-indigo-700 transition-colors !rounded-button text-center block mb-3">
                        <i class="ri-file-add-line mr-2"></i>
                        Apply Now
                    </a>
                    <a href="{{ route('internships.apply') }}" 
                       class="w-full bg-white border border-slate-200 text-slate-700 px-4 py-3 rounded-button hover:bg-slate-50 transition-colors !rounded-button text-center block text-sm">
                        General Application
                    </a>
                @else
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-3">
                        <div class="flex items-center space-x-2 mb-2">
                            <i class="ri-close-circle-line text-gray-600"></i>
                            <span class="text-gray-800 font-medium">Applications Closed</span>
                        </div>
                        <p class="text-sm text-gray-600">
                            The application deadline has passed.
                        </p>
                    </div>
                    <a href="{{ route('internships.apply') }}" 
                       class="w-full bg-slate-100 text-slate-600 px-4 py-3 rounded-button hover:bg-slate-200 transition-colors !rounded-button text-center block text-sm">
                        General Application
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function shareInternship(platform) {
    const url = window.location.href;
    const title = '{{ $internship->title }} at {{ $internship->company_name }}';
    
    if (platform === 'facebook') {
        window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`, '_blank');
    } else if (platform === 'twitter') {
        window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(title)}&url=${encodeURIComponent(url)}`, '_blank');
    } else if (platform === 'linkedin') {
        window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`, '_blank');
    }
}

function copyInternshipLink() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        alert('Internship link copied to clipboard!');
    });
}
</script>
@endpush
@endsection
