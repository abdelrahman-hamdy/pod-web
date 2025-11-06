@extends('layouts.app')

@section('title', $hackathon->title)

@section('content')
    
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <!-- Image Header -->
        <div class="h-80 bg-gradient-to-br from-indigo-50 to-purple-50 relative overflow-hidden">
            @if($hackathon->cover_image)
                <img src="{{ uploaded_file($hackathon->cover_image) }}" 
                     alt="{{ $hackathon->title }}" 
                     class="w-full h-full object-cover object-top" />
            @else
                <div class="w-full h-full bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center">
                    <div class="text-6xl text-indigo-300">
                        <i class="ri-trophy-line"></i>
                    </div>
                </div>
            @endif
            
            <!-- Date Badge -->
            <x-date-badge :date="$hackathon->start_date" />
            
            <!-- Category Badge -->
            @if($hackathon->category)
            <div class="absolute top-4 right-4">
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold text-white shadow-sm"
                      style="background-color: {{ $hackathon->category->color }}">
                    {{ $hackathon->category->name }}
                </span>
            </div>
            @endif
        </div>
        
        <!-- Content -->
        <div class="p-8">
            <!-- Title with Prize Floating Right -->
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1 min-w-0 pr-4">
                    <div class="flex items-center gap-3 mb-2">
                        <h1 class="text-3xl font-bold text-slate-800">{{ $hackathon->title }}</h1>
                        @if(auth()->check() && in_array(auth()->user()->role, ['superadmin', 'admin']))
                            <a href="{{ route('hackathons.edit', $hackathon) }}" 
                               class="inline-flex items-center px-3 py-1.5 bg-white border border-slate-200 text-slate-600 rounded-lg hover:bg-slate-50 transition-colors text-sm">
                                <i class="ri-edit-line mr-1.5"></i>
                                Edit
                            </a>
                        @endif
                    </div>
                    <div class="flex items-center space-x-2 text-sm text-slate-500">
                        <i class="ri-user-line"></i>
                        <span class="flex items-center">{{ $hackathon->creator->name ?? 'Organizer' }}<x-business-badge :user="$hackathon->creator" /></span>
                    </div>
                </div>
                
                <!-- Floating Prize -->
                @if($hackathon->prize_pool)
                <div class="flex-shrink-0 inline-flex items-center space-x-1 bg-green-50 text-green-700 px-4 py-2 rounded-lg">
                    <i class="ri-gift-line text-2xl mr-1"></i>
                    <span class="text-2xl font-bold whitespace-nowrap">{{ $hackathon->formatted_prize_pool }}</span>
                </div>
                @else
                <div class="flex-shrink-0 inline-flex items-center space-x-1 bg-slate-50 text-slate-600 px-4 py-2 rounded-lg">
                    <i class="ri-gift-line"></i>
                    <span class="text-base font-medium whitespace-nowrap">Free</span>
                </div>
                @endif
            </div>
            
            <!-- Description -->
            @if($hackathon->description)
            <div class="prose prose-slate max-w-none mb-6">
                {!! nl2br(e($hackathon->description)) !!}
            </div>
            @endif
            
            <!-- Metadata Grid -->
            <div class="grid {{ $hackathon->skill_requirements ? 'grid-cols-3' : 'grid-cols-2' }} gap-4 mb-6 pb-6 border-b border-slate-100">
                <!-- Date -->
                <div class="flex items-center space-x-3 text-sm text-slate-600">
                    <div class="flex-shrink-0 w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                        <i class="ri-calendar-check-line text-blue-600 text-lg"></i>
                    </div>
                    <div>
                        <div class="font-medium text-slate-800">{{ $hackathon->start_date->format('M d') }} - {{ $hackathon->end_date->format('M d') }}</div>
                        <div class="text-xs text-slate-500">{{ (int) $hackathon->start_date->diffInDays($hackathon->end_date) }} {{ (int) $hackathon->start_date->diffInDays($hackathon->end_date) == 1 ? 'day' : 'days' }}</div>
                    </div>
                </div>
                
                <!-- Teams -->
                <div class="flex items-center space-x-3 text-sm text-slate-600">
                    <div class="flex-shrink-0 w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center">
                        <i class="ri-user-line text-purple-600 text-lg"></i>
                    </div>
                    <div>
                        <div class="font-medium text-slate-800">{{ $hackathon->teams()->count() }} {{ $hackathon->teams()->count() == 1 ? 'team' : 'teams' }}</div>
                        <div class="text-xs text-slate-500">Joined</div>
                    </div>
                </div>

                <!-- Skill Level -->
                @if($hackathon->skill_requirements)
                <div class="flex items-center space-x-3 text-sm text-slate-600">
                    <div class="flex-shrink-0 w-10 h-10 bg-amber-50 rounded-lg flex items-center justify-center">
                        <i class="ri-trophy-line text-amber-600 text-lg"></i>
                    </div>
                    <div>
                        <div class="font-medium text-slate-800">{{ $hackathon->skill_requirements->getLabel() }}</div>
                        <div class="text-xs text-slate-500">Skill level</div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Technologies Tags -->
            @if($hackathon->technologies && is_array($hackathon->technologies) && count($hackathon->technologies) > 0)
            <div class="flex flex-wrap gap-2 mb-6 pb-6 border-b border-slate-100">
                @foreach($hackathon->technologies as $tech)
                    <span class="inline-flex items-center px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-full text-sm font-medium">
                        {{ $tech }}
                    </span>
                @endforeach
            </div>
            @endif

            <!-- Footer with Format, Location and Status -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    @if($hackathon->format->value === 'online')
                        <span class="inline-flex items-center space-x-1 px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg text-xs font-medium">
                            <i class="ri-wifi-line"></i>
                            <span>Online</span>
                        </span>
                    @elseif($hackathon->format->value === 'on-site')
                        <span class="inline-flex items-center space-x-1 px-3 py-1.5 bg-green-50 text-green-600 rounded-lg text-xs font-medium">
                            <i class="ri-building-line"></i>
                            <span>On-Site</span>
                        </span>
                    @else
                        <span class="inline-flex items-center space-x-1 px-3 py-1.5 bg-purple-50 text-purple-600 rounded-lg text-xs font-medium">
                            <i class="ri-group-line"></i>
                            <span>Hybrid</span>
                        </span>
                    @endif
                    @if(in_array($hackathon->format->value, ['on-site', 'hybrid']) && !empty($hackathon->location))
                        <span class="text-xs text-slate-500">•</span>
                        <span class="inline-flex items-center space-x-1 text-slate-600 text-xs font-medium">
                            <i class="ri-map-pin-line"></i>
                            <span class="truncate max-w-24">{{ Str::limit($hackathon->location, 20) }}</span>
                        </span>
                    @endif
                </div>
                
                @php
                    $status = $hackathon->getStatus();
                @endphp
                @if($status === 'upcoming')
                    <div class="flex items-center space-x-2">
                        <span class="text-xs text-slate-600">Starts in:</span>
                        <div class="flex items-center space-x-2 px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg text-xs font-semibold">
                            <i class="ri-time-line"></i>
                            <span class="countdown-{{ $hackathon->id }}" data-target-time="{{ $hackathon->start_date->timestamp }}">{{ $hackathon->start_date->diffForHumans() }}</span>
                        </div>
                    </div>
                @elseif($status === 'ongoing')
                    <div class="flex items-center space-x-2">
                        <span class="text-xs text-slate-600">Ends in:</span>
                        <div class="flex items-center space-x-2 px-3 py-1.5 bg-green-50 text-green-600 rounded-lg text-xs font-semibold">
                            <i class="ri-time-line"></i>
                            <span class="countdown-{{ $hackathon->id }}" data-target-time="{{ $hackathon->end_date->timestamp }}">{{ $hackathon->end_date->diffForHumans() }}</span>
                        </div>
                    </div>
                @else
                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 text-slate-600">
                        <i class="ri-checkbox-circle-line mr-1"></i>
                        Ended
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row lg:gap-8 gap-6 w-full justify-center mt-8">
        <!-- Main Content -->
        <div class="flex-1 w-full lg:max-w-3xl min-w-0 lg:flex-shrink-0">
            <!-- Rules & Guidelines -->
            @if($hackathon->rules)
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 mb-6">
                <h2 class="text-xl font-semibold text-slate-800 mb-4">Rules & Guidelines</h2>
                <div class="prose prose-slate max-w-none">
                    {!! nl2br(e($hackathon->rules)) !!}
                </div>
            </div>
            @endif

            <!-- Registration Widget -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 mb-6">
                <h2 class="text-xl font-semibold text-slate-800 mb-4">Registration</h2>
                
                <!-- Team Size Information -->
                <div class="mb-6">
                    <div class="flex items-center justify-between gap-6">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0 w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center">
                                <i class="ri-user-line text-purple-600 text-lg"></i>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-slate-800">{{ $hackathon->min_team_size }} - {{ $hackathon->max_team_size }} members</div>
                                <div class="text-xs text-slate-500">Team size</div>
                            </div>
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-slate-800">{{ $hackathon->teams()->count() }} {{ $hackathon->teams()->count() == 1 ? 'team' : 'teams' }}</div>
                            <div class="text-xs text-slate-500">Registered</div>
                        </div>
                    </div>
                </div>
                
                <!-- Hackathon Countdown -->
                <div class="mb-6 p-4 bg-slate-50 border border-slate-200 rounded-lg">
                    <div class="flex items-center space-x-2 mb-3">
                        <i class="ri-time-line text-indigo-600"></i>
                        <span class="text-sm font-medium text-slate-700">Time until hackathon starts</span>
                    </div>
                    <div class="flex items-center space-x-2 mb-3">
                        <i class="ri-calendar-check-line text-indigo-600"></i>
                        <span class="text-base font-semibold text-slate-800">{{ $hackathon->start_date->format('M j, Y \a\t g:i A') }}</span>
                    </div>
                    @if($hackathon->start_date > now())
                        <div id="countdown-{{ $hackathon->id }}" class="grid grid-cols-4 gap-2">
                            <div class="text-center py-3 px-2 bg-white rounded-lg border border-slate-200">
                                <div class="text-2xl font-bold text-indigo-600" id="days-{{ $hackathon->id }}">0</div>
                                <div class="text-xs text-slate-500 mt-1">Days</div>
                            </div>
                            <div class="text-center py-3 px-2 bg-white rounded-lg border border-slate-200">
                                <div class="text-2xl font-bold text-indigo-600" id="hours-{{ $hackathon->id }}">0</div>
                                <div class="text-xs text-slate-500 mt-1">Hours</div>
                            </div>
                            <div class="text-center py-3 px-2 bg-white rounded-lg border border-slate-200">
                                <div class="text-2xl font-bold text-indigo-600" id="minutes-{{ $hackathon->id }}">0</div>
                                <div class="text-xs text-slate-500 mt-1">Mins</div>
                            </div>
                            <div class="text-center py-3 px-2 bg-white rounded-lg border border-slate-200">
                                <div class="text-2xl font-bold text-indigo-600" id="seconds-{{ $hackathon->id }}">0</div>
                                <div class="text-xs text-slate-500 mt-1">Secs</div>
                            </div>
                        </div>
                    @else
                        <div class="text-sm text-green-600 font-medium flex items-center">
                            <i class="ri-play-circle-line mr-1"></i>
                            Hackathon has started
                        </div>
                    @endif
                </div>
                
                <!-- User's Team Status -->
                @auth
                    @if($userTeam)
                    <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 flex items-center justify-center">
                                <i class="ri-checkbox-circle-line text-green-600 text-2xl"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2 mb-1">
                                    <h4 class="text-sm font-semibold text-green-700">You're Registered</h4>
                                    @if($userTeam->leader_id === auth()->id())
                                        <span class="inline-flex items-center px-2 py-0.5 bg-indigo-100 text-indigo-600 rounded-full text-xs font-medium">Leader</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 bg-slate-100 text-slate-600 rounded-full text-xs font-medium">Member</span>
                                    @endif
                                </div>
                                <h3 class="text-base font-bold text-slate-800 mb-2">{{ $userTeam->name }}</h3>
                                <div class="flex items-center space-x-4 text-xs text-slate-600 mb-3">
                                    <span class="flex items-center space-x-1">
                                        <i class="ri-user-team-line"></i>
                                        <span>{{ $userTeam->members()->count() + 1 }} members</span>
                                    </span>
                                    <span class="flex items-center space-x-1">
                                        <i class="ri-calendar-line"></i>
                                        <span>{{ $hackathon->start_date->format('M d, Y') }}</span>
                                    </span>
                                </div>
                                <a href="{{ route('home.hackathons.teams.show', $userTeam) }}" class="inline-flex items-center text-sm font-medium text-green-700 hover:text-green-800">
                                    View Team Details →
                                </a>
                            </div>
                        </div>
                    </div>
                    @elseif($hackathon->isRegistrationOpen())
                    <!-- Registration Hint and Button -->
                    <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-start space-x-2 mb-3">
                            <i class="ri-information-line text-blue-600 mt-0.5"></i>
                            <p class="text-sm text-blue-700">
                                To participate in this hackathon, you need to create a new team or join an existing one.
                            </p>
                        </div>
                        <a href="{{ route('home.hackathons.teams') }}" 
                           class="w-full inline-flex items-center justify-center px-4 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            <i class="ri-team-line mr-2"></i>
                            View Teams
                        </a>
                    </div>
                    @else
                    <div class="text-center py-3 px-4 bg-slate-50 text-slate-600 rounded-lg">
                        <i class="ri-time-line mr-2"></i>
                        Registration Closed
                    </div>
                    @endif
                @else
                <a href="{{ route('login') }}" 
                   class="w-full inline-flex items-center justify-center px-4 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="ri-login-box-line mr-2"></i>
                    Login to Register
                </a>
                @endauth
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="w-full lg:w-80 lg:flex-shrink-0 min-w-0">
            <div class="space-y-6">

                <!-- Hackathon Statistics -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                    <h3 class="font-semibold text-slate-800 mb-4">Statistics</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-600">Teams Registered</span>
                            <span class="text-sm font-semibold text-slate-800">{{ $hackathon->teams()->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-600">Duration</span>
                            <span class="text-sm font-semibold text-slate-800">{{ (int) $hackathon->start_date->diffInDays($hackathon->end_date) }} days</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-600">Format</span>
                            <span class="text-sm font-semibold text-slate-800">{{ $hackathon->format->getLabel() }}</span>
                        </div>
                        @if($hackathon->skill_requirements)
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-600">Skill Level</span>
                            <span class="text-sm font-semibold text-slate-800">{{ $hackathon->skill_requirements->getLabel() }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Organizer -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                    <h3 class="font-semibold text-slate-800 mb-4">Organizer</h3>
                    <a href="{{ route('profile.show.other', $hackathon->creator) }}" class="flex items-center space-x-3 hover:bg-slate-50 rounded-lg p-2 -m-2 transition-colors group">
                        <x-avatar 
                            :src="$hackathon->creator->avatar ?? null"
                            :name="$hackathon->creator->name ?? 'User'"
                            size="sm"
                            :color="$hackathon->creator->avatar_color ?? null" />
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-800 group-hover:text-indigo-600 transition-colors truncate flex items-center">{{ $hackathon->creator->name }}<x-business-badge :user="$hackathon->creator" /></p>
                            <p class="text-xs text-slate-500 truncate">{{ $hackathon->creator->job_title ?? 'Organizer' }}</p>
                        </div>
                        <div class="w-2 h-2 bg-green-500 rounded-full flex-shrink-0"></div>
                    </a>
                </div>

                <!-- Share -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                    <h3 class="font-semibold text-slate-800 mb-4">Share This Hackathon</h3>
                    <div class="space-y-2">
                        <button onclick="shareOnFacebook()" class="w-full bg-blue-600 text-white px-4 py-2.5 rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center">
                            <i class="ri-facebook-line mr-2"></i>
                            Facebook
                        </button>
                        <button onclick="shareOnX()" class="w-full bg-slate-800 text-white px-4 py-2.5 rounded-lg hover:bg-slate-900 transition-colors flex items-center justify-center">
                            <i class="ri-twitter-x-line mr-2"></i>
                            X
                        </button>
                        <button onclick="shareOnLinkedIn()" class="w-full bg-blue-700 text-white px-4 py-2.5 rounded-lg hover:bg-blue-800 transition-colors flex items-center justify-center">
                            <i class="ri-linkedin-line mr-2"></i>
                            LinkedIn
                        </button>
                        <button onclick="copyHackathonLink()" class="w-full bg-slate-600 text-white px-4 py-2.5 rounded-lg hover:bg-slate-700 transition-colors flex items-center justify-center">
                            <i class="ri-links-line mr-2"></i>
                            Copy Link
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
function copyHackathonLink() {
    navigator.clipboard.writeText(window.location.href).then(function() {
        alert('Hackathon link copied to clipboard!');
    });
}

function shareOnFacebook() {
    window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(window.location.href), '_blank');
}

function shareOnX() {
    window.open('https://x.com/intent/tweet?url=' + encodeURIComponent(window.location.href) + '&text=' + encodeURIComponent('{{ $hackathon->title }}'), '_blank');
}

function shareOnLinkedIn() {
    window.open('https://www.linkedin.com/sharing/share-offsite/?url=' + encodeURIComponent(window.location.href), '_blank');
}

// Countdown functionality
function updateCountdown(hackathonId, targetDate) {
    const now = new Date().getTime();
    const distance = targetDate - now;

    if (distance < 0) {
        document.getElementById(`countdown-${hackathonId}`).innerHTML = '<div class="text-sm text-indigo-600 font-medium">Hackathon has started</div>';
        return;
    }

    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

    document.getElementById(`days-${hackathonId}`).innerHTML = days;
    document.getElementById(`hours-${hackathonId}`).innerHTML = hours;
    document.getElementById(`minutes-${hackathonId}`).innerHTML = minutes;
    document.getElementById(`seconds-${hackathonId}`).innerHTML = seconds;
}

// Initialize countdown for detailed view in registration widget
document.addEventListener('DOMContentLoaded', function() {
    const hackathonDate = new Date('{{ $hackathon->start_date->toISOString() }}').getTime();
    
    // Check if the detailed countdown widget exists
    if (document.getElementById('countdown-{{ $hackathon->id }}')) {
        updateCountdown({{ $hackathon->id }}, hackathonDate);
        
        // Update every second
        setInterval(function() {
            updateCountdown({{ $hackathon->id }}, hackathonDate);
        }, 1000);
    }
    
    // Initialize countdown for status badge
    function initStatusCountdown() {
        const countdownElements = document.querySelectorAll('[class^="countdown-"]');
        
        countdownElements.forEach(function(element) {
            const hackathonId = element.className.match(/countdown-(\d+)/)?.[1];
            if (!hackathonId) return;
            
            const targetTime = parseInt(element.getAttribute('data-target-time'));
            if (!targetTime) return;
            
            function updateCountdown() {
                const now = Math.floor(Date.now() / 1000);
                const distance = targetTime - now;
                
                if (distance < 0) {
                    element.textContent = 'Started';
                    return;
                }
                
                const days = Math.floor(distance / (60 * 60 * 24));
                const hours = Math.floor((distance % (60 * 60 * 24)) / (60 * 60));
                const minutes = Math.floor((distance % (60 * 60)) / 60);
                const seconds = Math.floor(distance % 60);
                
                if (days > 0) {
                    element.textContent = `${days}d ${hours}h ${minutes}m ${seconds}s`;
                } else if (hours > 0) {
                    element.textContent = `${hours}h ${minutes}m ${seconds}s`;
                } else if (minutes > 0) {
                    element.textContent = `${minutes}m ${seconds}s`;
                } else {
                    element.textContent = `${seconds}s`;
                }
            }
            
            // Update immediately
            updateCountdown();
            
            // Update every second
            setInterval(updateCountdown, 1000);
        });
    }
    
    // Initialize status countdown
    initStatusCountdown();
});
</script>
@endsection