@props(['hackathon'])

@php
    $status = $hackathon->getStatus();
    $participantCount = $hackathon->teams()->count(); // Number of teams for now
    $daysRemaining = $hackathon->registration_deadline->diffForHumans(['parts' => 2, 'short' => true]);
    $duration = (int) $hackathon->start_date->diffInDays($hackathon->end_date);
@endphp

<a href="{{ route('hackathons.show', $hackathon) }}" class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg hover:shadow-indigo-100/50 transition-all group block">
    <!-- Image Header -->
    <div class="h-56 bg-gradient-to-br from-indigo-50 to-purple-50 relative overflow-hidden">
        @if($hackathon->cover_image)
            <img src="{{ uploaded_file($hackathon->cover_image) }}" 
                 alt="{{ $hackathon->title }}" 
                 class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-500" />
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
    <div class="p-6">
        <!-- Title with Prize Floating Right -->
        <div class="flex items-start justify-between mb-3">
            <div class="flex-1 min-w-0 pr-4">
                <h3 class="text-xl font-bold text-slate-800 mb-1 group-hover:text-indigo-600 transition-colors">{{ $hackathon->title }}</h3>
                <div class="flex items-center space-x-2 text-sm text-slate-500">
                    <i class="ri-user-line"></i>
                    <span>{{ $hackathon->creator->name ?? 'Organizer' }}</span>
                </div>
            </div>
            
            <!-- Floating Prize -->
            @if($hackathon->prize_pool)
            <div class="flex-shrink-0 inline-flex items-center space-x-1 bg-green-50 text-green-700 px-3 py-1.5 rounded-lg">
                <i class="ri-gift-line text-xl"></i>
                <span class="text-lg font-bold whitespace-nowrap">{{ $hackathon->formatted_prize_pool }}</span>
            </div>
            @else
            <div class="flex-shrink-0 inline-flex items-center space-x-1 bg-slate-50 text-slate-600 px-3 py-1.5 rounded-lg">
                <i class="ri-gift-line"></i>
                <span class="text-sm font-medium whitespace-nowrap">Free</span>
            </div>
            @endif
        </div>
        
        <!-- Description -->
        <p class="text-slate-600 text-sm mb-4 line-clamp-2">
            {{ Str::limit($hackathon->description, 100) }}
        </p>
        
        <!-- Metadata Grid -->
        <div class="grid {{ $hackathon->skill_requirements ? 'grid-cols-3' : 'grid-cols-2' }} gap-3 mb-4 pb-4 border-b border-slate-100">
            <!-- Date -->
            <div class="flex items-center space-x-2 text-sm text-slate-600">
                <div class="flex-shrink-0 w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                    <i class="ri-calendar-check-line text-blue-600"></i>
                </div>
                <div class="min-w-0">
                    <div class="font-medium text-slate-800 truncate">{{ $hackathon->start_date->format('M d') }} - {{ $hackathon->end_date->format('M d') }}</div>
                    <div class="text-xs text-slate-500">{{ $duration }} {{ $duration == 1 ? 'day' : 'days' }}</div>
                </div>
            </div>
            
            <!-- Teams -->
            <div class="flex items-center space-x-2 text-sm text-slate-600">
                <div class="flex-shrink-0 w-8 h-8 bg-purple-50 rounded-lg flex items-center justify-center">
                    <i class="ri-user-line text-purple-600"></i>
                </div>
                <div class="min-w-0">
                    <div class="font-medium text-slate-800">{{ $participantCount }} {{ $participantCount == 1 ? 'team' : 'teams' }}</div>
                    <div class="text-xs text-slate-500">Joined</div>
                </div>
            </div>

            <!-- Skill Level -->
            @if($hackathon->skill_requirements)
            <div class="flex items-center space-x-2 text-sm text-slate-600">
                <div class="flex-shrink-0 w-8 h-8 bg-amber-50 rounded-lg flex items-center justify-center">
                    <i class="ri-trophy-line text-amber-600"></i>
                </div>
                <div class="min-w-0">
                    <div class="font-medium text-slate-800 truncate">{{ $hackathon->skill_requirements->getLabel() }}</div>
                    <div class="text-xs text-slate-500">Skill level</div>
                </div>
            </div>
            @endif
        </div>

        <!-- Technologies Tags -->
        @if($hackathon->technologies)
        <div class="flex flex-wrap gap-2 mb-4 pb-4 border-b border-slate-100">
            @foreach(array_slice($hackathon->technologies, 0, 3) as $tech)
                <span class="inline-flex items-center px-2.5 py-1 bg-indigo-50 text-indigo-600 rounded-full text-xs font-medium">
                    {{ $tech }}
                </span>
            @endforeach
            @if(count($hackathon->technologies) > 3)
                <span class="inline-flex items-center px-2.5 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-medium">
                    +{{ count($hackathon->technologies) - 3 }}
                </span>
            @endif
        </div>
        @endif

        <!-- Footer with Format and Countdown/Status -->
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
</a>