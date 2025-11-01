@extends('layouts.app')

@section('title', 'Events - People Of Data')

@section('content')
<x-page-header 
    title="Events"
    description="Discover and join amazing data science events in your area"
    actionUrl="{{ auth()->check() && auth()->user()->canCreateEvents() ? route('events.create') : '' }}"
    actionText="{{ auth()->check() && auth()->user()->canCreateEvents() ? 'Create Event' : '' }}"
/>

<!-- Filters -->
<x-filters-section alpine-data="eventFilters()" all-label="All Events">
    <!-- Date Range Filter -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" 
                        class="flex items-center space-x-2 px-4 py-2 border border-slate-200 rounded-button text-sm hover:bg-slate-50 !rounded-button whitespace-nowrap">
                    <div class="w-4 h-4 flex items-center justify-center">
                        <i class="ri-calendar-line"></i>
                    </div>
                    <span x-text="selectedDateRange || 'Date Range'"></span>
                    <div class="w-4 h-4 flex items-center justify-center">
                        <i class="ri-arrow-down-s-line"></i>
                    </div>
                </button>
                <div x-show="open" @click.away="open = false" 
                     class="absolute top-full left-0 mt-2 w-48 bg-white border border-slate-200 rounded-lg shadow-lg z-10">
                    <div class="p-2">
                        <button @click="filterByDateRange('all'); open = false" 
                                class="w-full text-left px-3 py-2 text-sm hover:bg-slate-50 rounded">
                            All Dates
                        </button>
                        <button @click="filterByDateRange('today'); open = false" 
                                class="w-full text-left px-3 py-2 text-sm hover:bg-slate-50 rounded">
                            Today
                        </button>
                        <button @click="filterByDateRange('this_week'); open = false" 
                                class="w-full text-left px-3 py-2 text-sm hover:bg-slate-50 rounded">
                            This Week
                        </button>
                        <button @click="filterByDateRange('this_month'); open = false" 
                                class="w-full text-left px-3 py-2 text-sm hover:bg-slate-50 rounded">
                            This Month
                        </button>
                        <button @click="filterByDateRange('next_month'); open = false" 
                                class="w-full text-left px-3 py-2 text-sm hover:bg-slate-50 rounded">
                            Next Month
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Event Type Filter -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" 
                        class="flex items-center space-x-2 px-4 py-2 border border-slate-200 rounded-button text-sm hover:bg-slate-50 !rounded-button whitespace-nowrap">
                    <div class="w-4 h-4 flex items-center justify-center">
                        <i class="ri-layout-line"></i>
                    </div>
                    <span x-text="selectedEventType || 'Event Type'"></span>
                    <div class="w-4 h-4 flex items-center justify-center">
                        <i class="ri-arrow-down-s-line"></i>
                    </div>
                </button>
                <div x-show="open" @click.away="open = false" 
                     class="absolute top-full left-0 mt-2 w-48 bg-white border border-slate-200 rounded-lg shadow-lg z-10">
                    <div class="p-2">
                        <button @click="filterByEventType('all'); open = false" 
                                class="w-full text-left px-3 py-2 text-sm hover:bg-slate-50 rounded">
                            All Types
                        </button>
                        <button @click="filterByEventType('online'); open = false" 
                                class="w-full text-left px-3 py-2 text-sm hover:bg-slate-50 rounded">
                            Online
                        </button>
                        <button @click="filterByEventType('in-person'); open = false" 
                                class="w-full text-left px-3 py-2 text-sm hover:bg-slate-50 rounded">
                            In-Person
                        </button>
                        <button @click="filterByEventType('hybrid'); open = false" 
                                class="w-full text-left px-3 py-2 text-sm hover:bg-slate-50 rounded">
                            Hybrid
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Search -->
            <div class="relative flex-1 max-w-sm">
                <input type="text" 
                       x-model="searchQuery" 
                       @input.debounce.500ms="searchEvents()"
                       placeholder="Search events..." 
                       class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-button text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
                <div class="absolute left-3 top-2.5 text-slate-400 w-4 h-4 flex items-center justify-center">
                    <i class="ri-search-line"></i>
                </div>
            </div>
</x-filters-section>

<div class="flex flex-col lg:flex-row lg:gap-8 gap-6 w-full justify-center">
    <!-- Events List -->
    <div class="flex-1 w-full lg:max-w-3xl min-w-0 lg:flex-shrink-0">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" id="events-container">
            @foreach($events as $event)
            <a href="{{ route('events.show', $event) }}" class="block bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg hover:shadow-indigo-100/50 transition-all group relative">
                <div class="block h-48 bg-gradient-to-br from-indigo-50 to-purple-50 relative overflow-hidden">
                    @if($event->banner_image)
                        <img src="{{ Storage::url($event->banner_image) }}" alt="{{ $event->title }}" class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-300" />
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center">
                            <div class="text-6xl text-indigo-300">
                                <i class="ri-calendar-event-line"></i>
                            </div>
                        </div>
                    @endif
                    <x-date-badge :date="$event->start_date" />
                    @if($event->category)
                        <div class="absolute top-4 right-4 px-3 py-1 rounded-full text-xs font-medium text-white" 
                             style="background-color: {{ $event->category->color }}">
                            {{ $event->category->name }}
                        </div>
                    @endif
                    
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-semibold text-slate-800 mb-3 group-hover:text-indigo-600 transition-colors">{{ $event->title }}</h3>
                    
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center space-x-1 text-sm">
                            <div class="w-4 h-4 flex items-center justify-center text-slate-600">
                                <i class="ri-time-line"></i>
                            </div>
                            <span class="text-slate-600">{{ $event->start_date->format('M j, Y') }}</span>
                            <span class="text-slate-400">•</span>
                            <span class="text-slate-400">{{ $event->start_date->format('g:i A') }}</span>
                            @if($event->end_date)
                                <span class="text-slate-400 mx-1">-</span>
                                <span class="text-slate-400">{{ $event->end_date->format('g:i A') }}</span>
                            @endif
                        </div>
                        
                        <div class="flex items-center space-x-1 text-sm">
                            <div class="w-4 h-4 flex items-center justify-center">
                                <i class="ri-map-pin-line text-indigo-600"></i>
                            </div>
                            @if($event->format === 'online')
                                <span class="text-indigo-600 font-medium">Online</span>
                            @elseif($event->format === 'hybrid')
                                <span class="text-indigo-600 font-medium">{{ Str::limit($event->location, 25) }} • Hybrid</span>
                            @else
                                <span class="text-indigo-600 font-medium">{{ Str::limit($event->location, 30) }}</span>
                            @endif
                        </div>
                    </div>
                    <p class="text-slate-600 mb-4">
                        {{ Str::limit($event->description, 120) }}
                    </p>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center space-x-1 text-indigo-600">
                                <div class="w-4 h-4 flex items-center justify-center">
                                    <i class="ri-user-line"></i>
                                </div>
                                <span class="text-sm font-bold">{{ $event->confirmedRegistrations()->count() }} attending</span>
                            </div>
                            @if($event->max_attendees)
                                <span class="text-sm text-slate-500">{{ $event->getAvailableSpots() }} spots left</span>
                            @endif
                        </div>
                        @php
                            $now = now();
                            $startDate = $event->start_date;
                            $endDate = $event->end_date ?? $startDate->addHours(2);
                            
                            if ($startDate > $now) {
                                $status = 'upcoming';
                                $statusColor = 'bg-green-100 text-green-600';
                                $statusText = 'Upcoming';
                            } elseif ($startDate <= $now && $endDate >= $now) {
                                $status = 'ongoing';
                                $statusColor = 'bg-blue-100 text-blue-600';
                                $statusText = 'Live Now';
                            } else {
                                $status = 'ended';
                                $statusColor = 'bg-red-100 text-red-600';
                                $statusText = 'Ended';
                            }
                        @endphp
                        <span class="px-3 py-1 {{ $statusColor }} rounded-full text-xs font-medium">
                            {{ $statusText }}
                        </span>
                    </div>
                </div>
            </a>
            @endforeach
            
            @if($events->isEmpty())
                <x-empty-search-state
                    icon="ri-calendar-event-line"
                    iconBg="from-indigo-100 to-purple-100"
                    iconColor="text-indigo-400"
                    :filterKeys="['category', 'date_range', 'format', 'search', 'specific_date']"
                    clearFiltersFunction="clearAllFilters"
                    title="No Events Found"
                    titleFiltered="No Events Match Your Filters"
                    description="There are no events scheduled at the moment. Check back later for new events!"
                    descriptionFiltered="No events match your current filters. Try adjusting your search criteria or clearing the filters to see more events."
                />
            @endif
        </div>

        <!-- Load More -->
        @if($events->hasPages())
        <div id="load-more-container" class="mt-8 flex justify-center">
            <button id="load-more-btn" class="bg-white border border-slate-200 text-slate-600 px-6 py-3 rounded-button hover:bg-slate-50 transition-colors !rounded-button whitespace-nowrap" data-next-page="{{ $events->nextPageUrl() }}">
                <span id="load-more-text">Load More Events</span>
                <div id="load-more-spinner" class="hidden">
                    <div class="inline-block w-4 h-4 border-2 border-slate-300 border-t-slate-600 rounded-full animate-spin"></div>
                </div>
            </button>
        </div>
        @endif
    </div>

    <!-- Right Sidebar -->
    <div class="w-full lg:w-80 lg:flex-shrink-0 min-w-0">
        <div class="space-y-6">
            <!-- My Event Registrations Widget -->
            @if(auth()->check())
                <x-widgets.my-event-registrations :registrations="$userRegistrations ?? collect()" />
            @endif

            <!-- Event Statistics -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                <h3 class="font-semibold text-slate-800 mb-4">Event Statistics</h3>
                <div class="space-y-4">
                    @php
                        $upcomingEvents = \App\Models\Event::where('start_date', '>', now())->where('is_active', true)->count();
                        $pastEvents = \App\Models\Event::where('start_date', '<', now())->where('is_active', true)->count();
                        $totalAttendees = \App\Models\EventRegistration::where('status', 'confirmed')->count();
                        $popularCategory = \App\Models\EventCategory::withCount('events')
                            ->where('is_active', true)
                            ->orderBy('events_count', 'desc')
                            ->first();
                    @endphp
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600">Upcoming Events</span>
                        <span class="text-sm font-semibold text-green-600">{{ $upcomingEvents }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600">Past Events</span>
                        <span class="text-sm font-semibold text-red-600">{{ $pastEvents }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600">Total Attendees</span>
                        <span class="text-sm font-semibold text-indigo-600">{{ number_format($totalAttendees) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600">Popular Category</span>
                        <span class="text-sm font-semibold text-purple-600">{{ $popularCategory ? $popularCategory->name : 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Event Calendar -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                <h3 class="font-semibold text-slate-800 mb-4">Event Calendar</h3>
                @php
                    $currentMonth = now();
                    $firstDayOfMonth = $currentMonth->copy()->startOfMonth();
                    $lastDayOfMonth = $currentMonth->copy()->endOfMonth();
                    $firstDayOfWeek = $firstDayOfMonth->dayOfWeek;
                    $daysInMonth = $lastDayOfMonth->day;
                    
                    // Get events for this month
                    $monthEvents = \App\Models\Event::whereBetween('start_date', [
                        $firstDayOfMonth->startOfDay(),
                        $lastDayOfMonth->endOfDay()
                    ])->where('is_active', true)->get();
                    
                    // Create array of days with events
                    $eventDays = $monthEvents->map(function($event) {
                        return $event->start_date->day;
                    })->toArray();
                @endphp
                
                <div class="mb-4 flex items-center justify-between">
                    <h4 class="font-medium text-slate-800">{{ $currentMonth->format('F Y') }}</h4>
                </div>
                
                <div class="grid grid-cols-7 gap-1 text-center text-xs mb-4">
                    <div class="text-slate-500 font-medium p-2">S</div>
                    <div class="text-slate-500 font-medium p-2">M</div>
                    <div class="text-slate-500 font-medium p-2">T</div>
                    <div class="text-slate-500 font-medium p-2">W</div>
                    <div class="text-slate-500 font-medium p-2">T</div>
                    <div class="text-slate-500 font-medium p-2">F</div>
                    <div class="text-slate-500 font-medium p-2">S</div>
                    
                    @for($i = 0; $i < $firstDayOfWeek; $i++)
                        <div class="p-2"></div>
                    @endfor
                    
                    @for($day = 1; $day <= $daysInMonth; $day++)
                        @php
                            $hasEvent = in_array($day, $eventDays);
                            $isToday = $day == now()->day && $currentMonth->month == now()->month && $currentMonth->year == now()->year;
                        @endphp
                        <div class="p-2 cursor-pointer rounded hover:bg-slate-50 transition-colors {{ $hasEvent ? 'bg-indigo-100 text-indigo-600 font-medium' : 'text-slate-700' }} {{ $isToday ? 'ring-2 ring-indigo-500' : '' }}"
                             onclick="filterByDate('{{ $currentMonth->format('Y-m') }}-{{ sprintf('%02d', $day) }}')">
                            {{ $day }}
                        </div>
                    @endfor
                </div>
                
                <div class="text-center">
                    <button onclick="clearDateFilter()" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                        Clear Date Filter
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function eventFilters() {
        return {
            selectedCategory: null,
            selectedDateRange: null,
            selectedEventType: null,
            selectedSpecificDate: null,
            searchQuery: '',
            loading: false,
            
            // Category management
            categories: @json($categories->map(function($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'color' => $category->color
                ];
            })),
            visibleCategories: [],
            hiddenCategories: [],
            hasMoreCategories: false,
            
            init() {
                // Initialize from URL parameters
                const urlParams = new URLSearchParams(window.location.search);
                this.selectedCategory = urlParams.get('category') ? parseInt(urlParams.get('category')) : null;
                this.selectedDateRange = urlParams.get('date_range') || null;
                this.selectedEventType = urlParams.get('format') || null;
                this.searchQuery = urlParams.get('search') || '';
                
                // Initialize all categories as visible first
                this.visibleCategories = [...this.categories];
                this.hiddenCategories = [];
                this.hasMoreCategories = false;
                
                // Wait for DOM to render before calculating
                this.$nextTick(() => {
                    setTimeout(() => {
                        this.calculateVisibleCategories();
                    }, 50);
                });
                
                // Listen for window resize
                window.addEventListener('resize', () => {
                    this.handleResize();
                });
            },
            
            calculateVisibleCategories() {
                const container = this.$refs.categoryContainer;
                if (!container) {
                    return;
                }
                
                // Reset to show all categories temporarily for measurement
                this.visibleCategories = [...this.categories];
                this.hiddenCategories = [];
                this.hasMoreCategories = false;
                
                // Wait for DOM update
                this.$nextTick(() => {
                    const containerWidth = container.offsetWidth;
                    const horizontalPadding = 48; // p-6 = 24px each side = 48px total
                    const moreButtonWidth = 100; // Reserve space for "More" button
                    const spacingBuffer = 20; // Extra buffer for spacing
                    let availableWidth = containerWidth - horizontalPadding - moreButtonWidth - spacingBuffer;
                    
                    // Get all category buttons (exclude "All Events" button)
                    const buttons = Array.from(container.querySelectorAll('button')).slice(1);
                    let totalWidth = 0;
                    let visibleCount = 0;
                    
                    // Calculate cumulative width
                    for (let i = 0; i < buttons.length; i++) {
                        const buttonWidth = buttons[i].offsetWidth + 8; // 8px for space-x-2
                        
                        if (totalWidth + buttonWidth <= availableWidth) {
                            totalWidth += buttonWidth;
                            visibleCount++;
                        } else {
                            break;
                        }
                    }
                    
                    // If we can't fit all, ensure we have room for "More" button
                    if (visibleCount < this.categories.length) {
                        // Keep at least 2 visible
                        visibleCount = Math.max(2, visibleCount);
                        this.visibleCategories = this.categories.slice(0, visibleCount);
                        this.hiddenCategories = this.categories.slice(visibleCount);
                        this.hasMoreCategories = true;
                    } else {
                        this.visibleCategories = [...this.categories];
                        this.hiddenCategories = [];
                        this.hasMoreCategories = false;
                    }
                });
            },
            
            handleResize() {
                // Debounce resize events
                clearTimeout(this.resizeTimeout);
                this.resizeTimeout = setTimeout(() => {
                    this.calculateVisibleCategories();
                }, 100);
            },
            
            filterByCategory(categoryId) {
                this.selectedCategory = categoryId;
                this.applyFilters();
            },
            
            filterByDateRange(range) {
                this.selectedDateRange = range;
                this.applyFilters();
            },
            
            filterByEventType(type) {
                this.selectedEventType = type;
                this.applyFilters();
            },
            
            searchEvents() {
                this.applyFilters();
            },
            
            filterBySpecificDate(date) {
                this.selectedDateRange = null; // Clear date range filter
                this.applyFilters();
            },
            
            applyFilters() {
                this.loading = true;
                
                const params = new URLSearchParams();
                if (this.selectedCategory) params.append('category', this.selectedCategory);
                if (this.selectedDateRange && this.selectedDateRange !== 'all') params.append('date_range', this.selectedDateRange);
                if (this.selectedEventType && this.selectedEventType !== 'all') params.append('format', this.selectedEventType);
                if (this.searchQuery) params.append('search', this.searchQuery);
                if (this.selectedSpecificDate) params.append('specific_date', this.selectedSpecificDate);
                
                const url = `{{ route('events.index') }}?${params.toString()}`;
                
                fetch(url)
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        
                        // Update events container
                        const newEventsContainer = doc.querySelector('#events-container');
                        if (newEventsContainer) {
                            document.getElementById('events-container').innerHTML = newEventsContainer.innerHTML;
                        }
                        
                        // Update load more button
                        const newLoadMoreContainer = doc.querySelector('#load-more-container');
                        const currentLoadMoreContainer = document.querySelector('#load-more-container');
                        
                        if (newLoadMoreContainer && currentLoadMoreContainer) {
                            // Replace the entire load more container
                            currentLoadMoreContainer.outerHTML = newLoadMoreContainer.outerHTML;
                            
                            // Ensure the new button is properly enabled after DOM update
                            setTimeout(() => {
                                const newLoadMoreBtn = document.querySelector('#load-more-btn');
                                if (newLoadMoreBtn) {
                                    newLoadMoreBtn.disabled = false;
                                }
                            }, 10);
                        } else if (!newLoadMoreContainer && currentLoadMoreContainer) {
                            // Hide load more container if no more pages
                            currentLoadMoreContainer.style.display = 'none';
                        }
                        
                        // Update URL in browser
                        window.history.pushState({}, '', url);
                    })
                    .catch(error => {
                        console.error('Error filtering events:', error);
                        alert('Error filtering events. Please try again.');
                    })
                    .finally(() => {
                        this.loading = false;
                    });
            }
        }
    }

    // Load more functionality
    document.addEventListener("DOMContentLoaded", function () {
        // Use event delegation to handle dynamically added load more buttons
        document.addEventListener('click', function(e) {
            // Check if clicked element or its parent is the load more button
            let loadMoreBtn = e.target;
            if (loadMoreBtn.id !== 'load-more-btn') {
                loadMoreBtn = e.target.closest('#load-more-btn');
            }
            
            if (loadMoreBtn && loadMoreBtn.id === 'load-more-btn') {
                // Prevent multiple clicks
                if (loadMoreBtn.disabled) {
                    return;
                }
                
                const nextPageUrl = loadMoreBtn.getAttribute("data-next-page");
                
                if (!nextPageUrl) {
                    return;
                }

                // Show loading state immediately
                loadMoreBtn.disabled = true;
                const loadMoreText = loadMoreBtn.querySelector("#load-more-text");
                const loadMoreSpinner = loadMoreBtn.querySelector("#load-more-spinner");
                if (loadMoreText) loadMoreText.classList.add("hidden");
                if (loadMoreSpinner) loadMoreSpinner.classList.remove("hidden");

                // Fetch next page with current filters
                const currentParams = new URLSearchParams(window.location.search);
                const nextPageUrlWithFilters = nextPageUrl + (nextPageUrl.includes('?') ? '&' : '?') + currentParams.toString();
                
                fetch(nextPageUrlWithFilters)
                    .then(response => response.text())
                    .then(html => {
                        // Parse the response to extract events
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newEvents = doc.querySelectorAll('#events-container > div');
                        
                        // Append new events to container
                        const container = document.getElementById("events-container");
                        newEvents.forEach(event => {
                            container.appendChild(event);
                        });

                        // Update next page URL
                        const newNextPage = doc.querySelector('#load-more-btn')?.getAttribute("data-next-page");
                        
                        if (newNextPage) {
                            loadMoreBtn.setAttribute("data-next-page", newNextPage);
                        } else {
                            // No more pages, hide the button
                            loadMoreBtn.parentElement.style.display = 'none';
                        }
                    })
                    .catch(error => {
                        console.error('Error loading more events:', error);
                        alert('Error loading more events. Please try again.');
                    })
                    .finally(() => {
                        // Hide loading state
                        loadMoreBtn.disabled = false;
                        if (loadMoreText) loadMoreText.classList.remove("hidden");
                        if (loadMoreSpinner) loadMoreSpinner.classList.add("hidden");
                    });
            }
        });
    });

    // Global functions for calendar interaction
    function filterByDate(date) {
        // Get the Alpine.js component instance
        const eventFiltersComponent = Alpine.$data(document.querySelector('[x-data="eventFilters()"]'));
        if (eventFiltersComponent) {
            eventFiltersComponent.selectedSpecificDate = date;
            eventFiltersComponent.selectedDateRange = null; // Clear date range filter
            eventFiltersComponent.applyFilters();
        }
    }

    function clearDateFilter() {
        // Get the Alpine.js component instance
        const eventFiltersComponent = Alpine.$data(document.querySelector('[x-data="eventFilters()"]'));
        if (eventFiltersComponent) {
            eventFiltersComponent.selectedSpecificDate = null;
            eventFiltersComponent.applyFilters();
        }
    }

    function clearAllFilters() {
        // Get the Alpine.js component instance
        const eventFiltersComponent = Alpine.$data(document.querySelector('[x-data="eventFilters()"]'));
        if (eventFiltersComponent) {
            eventFiltersComponent.selectedCategory = null;
            eventFiltersComponent.selectedDateRange = null;
            eventFiltersComponent.selectedEventType = null;
            eventFiltersComponent.selectedSpecificDate = null;
            eventFiltersComponent.searchQuery = '';
            
            // Clear search input
            const searchInput = document.querySelector('input[name="search"]');
            if (searchInput) {
                searchInput.value = '';
            }
            
            // Clear date range select
            const dateRangeSelect = document.querySelector('select[name="date_range"]');
            if (dateRangeSelect) {
                dateRangeSelect.value = 'all';
            }
            
            // Clear event type select
            const eventTypeSelect = document.querySelector('select[name="event_type"]');
            if (eventTypeSelect) {
                eventTypeSelect.value = 'all';
            }
            
            eventFiltersComponent.applyFilters();
        }
    }
</script>
@endpush