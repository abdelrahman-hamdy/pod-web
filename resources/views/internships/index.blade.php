@extends('layouts.app')

@section('title', 'Internships - People Of Data')

@section('content')
<!-- Success Message -->
@if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)" 
         class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="ri-check-line mr-2 text-lg"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button @click="show = false" class="text-green-600 hover:text-green-800">
                <i class="ri-close-line text-xl"></i>
            </button>
        </div>
    </div>
@endif

<x-page-header 
    title="Internships"
    description="Discover internship opportunities to kickstart your career"
    actionUrl="{{ auth()->check() && auth()->user()->canCreateInternships() ? route('internships.create') : '' }}"
    actionText="{{ auth()->check() && auth()->user()->canCreateInternships() ? 'Add Internship' : '' }}"
    icon="ri-add-line"
/>

<!-- Tabs -->
@php
    $tabs = [
        'internships' => ['label' => 'Available Internships'],
        'applications' => ['label' => 'My Applications', 'count' => $myApplications->count()],
    ];
@endphp
<x-tabs :tabs="$tabs" />

<div class="w-full" x-data="{ activeTab: 'internships' }" @tab-switched.window="activeTab = $event.detail.tab">
    <!-- Internships Tab -->
    <div x-show="activeTab === 'internships'">
        <!-- Category Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 mb-8">
            <div class="flex items-center space-x-2 flex-wrap">
                <button @click="filterByCategory(null)" 
                        class="px-4 py-2 rounded-button text-sm font-medium !rounded-button whitespace-nowrap border border-slate-200 hover:bg-slate-50 transition-colors"
                        id="category-all">
                    All Categories
                </button>
                @foreach($categories as $category)
                    <button @click="filterByCategory({{ $category->id }})" 
                            class="px-4 py-2 rounded-button text-sm font-medium !rounded-button whitespace-nowrap border border-slate-200 hover:bg-slate-50 transition-colors"
                            id="category-{{ $category->id }}">
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Internships Grid -->
        @if($internships->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="internships-container">
                @foreach($internships as $internship)
                    <a href="{{ route('internships.show', $internship) }}" class="bg-white rounded-xl shadow-sm border border-slate-100 hover:shadow-lg hover:shadow-indigo-100/50 transition-all group block">
                        <!-- Content -->
                        <div class="p-6">
                            <!-- Category -->
                            @if($internship->category)
                            <div class="mb-3">
                                <span class="text-xs font-semibold uppercase tracking-wide" style="color: {{ $internship->category->color ?? '#3b82f6' }}">
                                    {{ $internship->category->name }}
                                </span>
                            </div>
                            @endif
                            
                            <!-- Title with Company -->
                            <div class="mb-3">
                                <h3 class="text-xl font-bold text-slate-800 mb-1 group-hover:text-indigo-600 transition-colors line-clamp-1">
                                    {{ $internship->title }}
                                </h3>
                                <div class="flex items-center space-x-2 text-sm text-slate-500">
                                    <i class="ri-building-line"></i>
                                    <span>{{ $internship->company_name }}</span>
                                </div>
                            </div>
                            
                            <!-- Description -->
                            <p class="text-slate-600 text-sm mb-4 line-clamp-2">
                                {{ Str::limit(strip_tags($internship->description), 100) }}
                            </p>
                            
                            <!-- Metadata Grid -->
                            <div class="grid grid-cols-2 gap-3 mb-4">
                                <!-- Location -->
                                <div class="flex items-center space-x-2 text-sm text-slate-600">
                                    <div class="flex-shrink-0 w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center">
                                        <i class="ri-map-pin-line text-indigo-600 text-xs"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-medium text-slate-800 truncate">{{ Str::limit($internship->location, 18) }}</div>
                                        <div class="text-xs text-slate-500">Location</div>
                                    </div>
                                </div>
                                
                                <!-- Type -->
                                <div class="flex items-center space-x-2 text-sm text-slate-600">
                                    <div class="flex-shrink-0 w-8 h-8 bg-purple-50 rounded-lg flex items-center justify-center">
                                        <i class="ri-time-line text-purple-600 text-xs"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-medium text-slate-800 truncate">{{ ucfirst(str_replace('_', ' ', $internship->type)) }}</div>
                                        <div class="text-xs text-slate-500">Type</div>
                                    </div>
                                </div>
                                
                                @if($internship->duration)
                                <!-- Duration -->
                                <div class="flex items-center space-x-2 text-sm text-slate-600">
                                    <div class="flex-shrink-0 w-8 h-8 bg-green-50 rounded-lg flex items-center justify-center">
                                        <i class="ri-calendar-check-line text-green-600 text-xs"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-medium text-slate-800 truncate">{{ $internship->duration }}</div>
                                        <div class="text-xs text-slate-500">Duration</div>
                                    </div>
                                </div>
                                @endif
                                
                                <!-- Deadline -->
                                <div class="flex items-center space-x-2 text-sm text-slate-600">
                                    <div class="flex-shrink-0 w-8 h-8 bg-orange-50 rounded-lg flex items-center justify-center">
                                        <i class="ri-time-line text-orange-600 text-xs"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-medium text-slate-800 truncate">
                                            {{ is_string($internship->application_deadline) ? \Carbon\Carbon::parse($internship->application_deadline)->format('M d') : $internship->application_deadline->format('M d') }}
                                        </div>
                                        <div class="text-xs text-slate-500">Deadline</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <!-- Load More -->
            @if($internships->hasPages())
            <div id="load-more-container" class="mt-8 flex justify-center">
                <button id="load-more-btn" class="bg-white border border-slate-200 text-slate-600 px-6 py-3 rounded-lg hover:bg-slate-50 transition-colors" data-next-page="{{ $internships->nextPageUrl() }}">
                    <span id="load-more-text">Load More Internships</span>
                    <div id="load-more-spinner" class="hidden">
                        <div class="inline-block w-4 h-4 border-2 border-slate-300 border-t-slate-600 rounded-full animate-spin"></div>
                    </div>
                </button>
            </div>
            @endif
        @else
            <x-empty-search-state
                icon="ri-graduation-cap-line"
                iconBg="from-indigo-100 to-purple-100"
                iconColor="text-indigo-400"
                title="No Internships Available"
                description="There are no internship listings available at the moment. Check back later for new opportunities!"
            />
        @endif
    </div>

    <!-- Applications Tab -->
    <div x-show="activeTab === 'applications'" style="display: none;">
        @if($myApplications->count() > 0)
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-slate-800">My Applications</h3>
                    <a href="{{ route('internships.apply') }}" 
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm">
                        <i class="ri-add-line mr-2"></i>
                        Create Application
                    </a>
                </div>
                <div class="space-y-4">
                    @foreach($myApplications as $application)
                        <div class="border border-slate-200 rounded-lg p-4 hover:bg-slate-50 transition-colors">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-slate-800 mb-1">
                                        @if($application->internship)
                                            {{ $application->internship->title }}
                                        @else
                                            General Application
                                        @endif
                                    </h4>
                                    @if($application->internship)
                                        <p class="text-sm text-slate-600 mb-2">{{ $application->internship->company_name }}</p>
                                    @endif
                                    <p class="text-xs text-slate-500">Applied on {{ $application->created_at->format('M d, Y') }}</p>
                                </div>
                                <span class="px-3 py-1 rounded-full text-xs font-medium
                                    @if($application->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($application->status === 'under_review') bg-blue-100 text-blue-800
                                    @elseif($application->status === 'accepted') bg-green-100 text-green-800
                                    @else bg-red-100 text-red-800 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-12 text-center">
                <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="ri-file-list-3-line text-2xl text-indigo-600"></i>
                </div>
                <h3 class="text-lg font-semibold text-slate-800 mb-2">No applications yet</h3>
                <p class="text-slate-600 mb-6">You haven't applied for any internships yet</p>
                <a href="{{ route('internships.apply') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="ri-add-line mr-2"></i>
                    Apply for Internship
                </a>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function filterByCategory(categoryId) {
    const params = new URLSearchParams();
    if (categoryId) params.append('category', categoryId);
    
    const url = `{{ route('internships.index') }}?${params.toString()}`;
    
    // Update active state
    document.querySelectorAll('[id^="category-"]').forEach(btn => {
        btn.classList.remove('bg-indigo-600', 'text-white', 'border-indigo-600');
        btn.classList.add('border-slate-200');
    });
    
    const activeBtn = document.getElementById(categoryId ? `category-${categoryId}` : 'category-all');
    if (activeBtn) {
        activeBtn.classList.add('bg-indigo-600', 'text-white', 'border-indigo-600');
        activeBtn.classList.remove('border-slate-200');
    }
    
    // Fetch new data
    fetch(url)
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            const newContainer = doc.querySelector('#internships-container');
            if (newContainer) {
                document.getElementById('internships-container').innerHTML = newContainer.innerHTML;
            }
            
            const newLoadMore = doc.querySelector('#load-more-container');
            const currentLoadMore = document.querySelector('#load-more-container');
            
            if (newLoadMore && currentLoadMore) {
                currentLoadMore.outerHTML = newLoadMore.outerHTML;
            } else if (!newLoadMore && currentLoadMore) {
                currentLoadMore.style.display = 'none';
            }
        })
        .catch(error => console.error('Error:', error));
    
    window.history.pushState({}, '', url);
}

// Load more functionality
document.addEventListener('click', function(e) {
    if (e.target.id === 'load-more-btn' || e.target.closest('#load-more-btn')) {
        const btn = e.target.id === 'load-more-btn' ? e.target : e.target.closest('#load-more-btn');
        const nextPageUrl = btn.getAttribute('data-next-page');
        
        if (!nextPageUrl || btn.disabled) return;
        
        btn.disabled = true;
        const loadMoreText = btn.querySelector('#load-more-text');
        const loadMoreSpinner = btn.querySelector('#load-more-spinner');
        
        if (loadMoreText) loadMoreText.classList.add('hidden');
        if (loadMoreSpinner) loadMoreSpinner.classList.remove('hidden');
        
        fetch(nextPageUrl)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newInternships = doc.querySelectorAll('#internships-container > a');
                
                const container = document.getElementById('internships-container');
                newInternships.forEach(internship => container.appendChild(internship));
                
                const newNextPage = doc.querySelector('#load-more-btn')?.getAttribute('data-next-page');
                if (newNextPage) {
                    btn.setAttribute('data-next-page', newNextPage);
                } else {
                    btn.parentElement.style.display = 'none';
                }
            })
            .catch(error => console.error('Error:', error))
            .finally(() => {
                btn.disabled = false;
                if (loadMoreText) loadMoreText.classList.remove('hidden');
                if (loadMoreSpinner) loadMoreSpinner.classList.add('hidden');
            });
    }
});

// Initialize active category
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const category = urlParams.get('category');
    
    const activeBtn = document.getElementById(category ? `category-${category}` : 'category-all');
    if (activeBtn) {
        activeBtn.classList.add('bg-indigo-600', 'text-white', 'border-indigo-600');
        activeBtn.classList.remove('border-slate-200');
    }
});
</script>
@endpush
@endsection
