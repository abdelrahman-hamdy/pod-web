@extends('layouts.app')

@section('title', 'Teams - Hackathons Dashboard')

@section('content')
<div class="w-full">
    <!-- Flash Messages -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center justify-between">
            <span>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
                <i class="ri-close-line"></i>
            </button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center justify-between">
            <span>{{ session('error') }}</span>
            <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900">
                <i class="ri-close-line"></i>
            </button>
        </div>
    @endif
    
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-slate-800 mb-2">Teams</h1>
            <p class="text-slate-600">Manage your hackathon teams and collaborations</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="openCreateTeamModal()" class="bg-indigo-600 text-white px-6 py-3 rounded-button hover:bg-indigo-700 transition-colors flex items-center space-x-2 !rounded-button whitespace-nowrap">
                <div class="w-5 h-5 flex items-center justify-center">
                    <i class="ri-add-line"></i>
                </div>
                <span>Create Team</span>
            </button>
            <a href="{{ route('hackathons.index') }}" class="border border-slate-300 text-slate-700 px-6 py-3 rounded-button hover:bg-slate-50 transition-colors flex items-center space-x-2 !rounded-button whitespace-nowrap">
                <div class="w-5 h-5 flex items-center justify-center">
                    <i class="ri-trophy-line"></i>
                </div>
                <span>Browse Hackathons</span>
            </a>
        </div>
    </div>

    <!-- Tabs -->
    @php
        $tabs = [
            'my-teams' => ['label' => 'My Teams'],
            'available-teams' => ['label' => 'Available Teams'],
            'received-invitations' => ['label' => 'Received Invitations'],
            'received-requests' => ['label' => 'Join Requests', 'count' => $receivedJoinRequests->count()],
            'sent-requests' => ['label' => 'Sent Join Requests'],
        ];
    @endphp
    <x-tabs :tabs="$tabs" />

    <div class="w-full" x-data="{ activeTab: 'my-teams' }" @tab-switched.window="activeTab = $event.detail.tab">
        <!-- Tab Content -->
        <div x-show="activeTab === 'my-teams'">
            <!-- My Teams Content -->
            <!-- Teams I Lead -->
            @if($leadingTeams->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 mb-8">
                    <h3 class="text-lg font-semibold text-slate-800 mb-6">Teams I Lead</h3>
                    <div class="space-y-4">
                        @foreach($leadingTeams as $team)
                            <div class="border border-slate-200 rounded-lg p-6 hover:bg-slate-50 transition-colors">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3 mb-2">
                                            <h4 class="font-semibold text-slate-800">{{ $team->name }}</h4>
                                            @if($team->is_public)
                                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-600 rounded-full">Public - Accepting Members</span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-slate-600 mb-3">{{ Str::limit($team->description, 120) }}</p>
                                        <div class="flex items-center space-x-4 text-sm text-slate-500">
                                            <span><i class="ri-trophy-line mr-1"></i>{{ $team->hackathon->title }}</span>
                                            <span><i class="ri-team-line mr-1"></i>{{ $team->member_count }}/{{ $team->hackathon->max_team_size }} members</span>
                                            <span><i class="ri-calendar-line mr-1"></i>{{ $team->hackathon->start_date->format('M d, Y') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="{{ route('home.hackathons.teams.show', $team) }}" 
                                           class="px-4 py-2 text-sm bg-indigo-600 text-white hover:bg-indigo-700 rounded-lg transition-colors font-medium">
                                            View Team
                                        </a>
                                        <a href="{{ route('hackathons.show', $team->hackathon) }}" 
                                           class="px-4 py-2 text-sm bg-slate-600 text-white hover:bg-slate-700 rounded-lg transition-colors font-medium">
                                            View Hackathon
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Team Members -->
                                <div class="border-t border-slate-100 pt-4">
                                    <h5 class="text-sm font-medium text-slate-700 mb-3">Team Members</h5>
                                    <div class="flex flex-wrap gap-3">
                                        <!-- Team Leader -->
                                        <div class="flex items-center space-x-2 bg-indigo-50 rounded-lg px-3 py-2">
                                            <x-avatar 
                                                :src="$team->leader->avatar ?? null"
                                                :name="$team->leader->name ?? 'User'"
                                                size="sm"
                                                :color="$team->leader->avatar_color ?? null" />
                                            <div>
                                                <p class="text-sm font-medium text-slate-700 flex items-center">{{ $team->leader->name }}<x-business-badge :user="$team->leader" /></p>
                                                <p class="text-xs text-slate-500">Leader</p>
                                            </div>
                                        </div>
                                        
                                        <!-- Team Members -->
                                        @foreach($team->members as $member)
                                            <div class="flex items-center space-x-2 bg-slate-50 rounded-lg px-3 py-2">
                                                <x-avatar 
                                                    :src="$member->user->avatar ?? null"
                                                    :name="$member->user->name ?? 'User'"
                                                    size="sm"
                                                    :color="$member->user->avatar_color ?? null" />
                                                <div>
                                                    <p class="text-sm font-medium text-slate-700 flex items-center">{{ $member->user->name }}<x-business-badge :user="$member->user" /></p>
                                                    <p class="text-xs text-slate-500">Member</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Teams I'm Member Of -->
            @if($memberTeams->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 mb-8">
                    <h3 class="text-lg font-semibold text-slate-800 mb-6">Teams I'm Member Of</h3>
                    <div class="space-y-4">
                        @foreach($memberTeams as $team)
                            <div class="border border-slate-200 rounded-lg p-6 hover:bg-slate-50 transition-colors">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3 mb-2">
                                            <h4 class="font-semibold text-slate-800">{{ $team->name }}</h4>
                                        </div>
                                        <p class="text-sm text-slate-600 mb-3">{{ Str::limit($team->description, 120) }}</p>
                                        <div class="flex items-center space-x-4 text-sm text-slate-500">
                                            <span><i class="ri-trophy-line mr-1"></i>{{ $team->hackathon->title }}</span>
                                            <span><i class="ri-team-line mr-1"></i>{{ $team->member_count }}/{{ $team->hackathon->max_team_size }} members</span>
                                            <span><i class="ri-calendar-line mr-1"></i>{{ $team->hackathon->start_date->format('M d, Y') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="{{ route('home.hackathons.teams.show', $team) }}" 
                                           class="px-4 py-2 text-sm bg-indigo-600 text-white hover:bg-indigo-700 rounded-lg transition-colors font-medium">
                                            View Team
                                        </a>
                                        <a href="{{ route('hackathons.show', $team->hackathon) }}" 
                                           class="px-4 py-2 text-sm bg-slate-600 text-white hover:bg-slate-700 rounded-lg transition-colors font-medium">
                                            View Hackathon
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Team Members -->
                                <div class="border-t border-slate-100 pt-4">
                                    <h5 class="text-sm font-medium text-slate-700 mb-3">Team Members</h5>
                                    <div class="flex flex-wrap gap-3">
                                        <!-- Team Leader -->
                                        <div class="flex items-center space-x-2 bg-indigo-50 rounded-lg px-3 py-2">
                                            <x-avatar 
                                                :src="$team->leader->avatar ?? null"
                                                :name="$team->leader->name ?? 'User'"
                                                size="sm"
                                                :color="$team->leader->avatar_color ?? null" />
                                            <div>
                                                <p class="text-sm font-medium text-slate-700 flex items-center">{{ $team->leader->name }}<x-business-badge :user="$team->leader" /></p>
                                                <p class="text-xs text-slate-500">Leader</p>
                                            </div>
                                        </div>
                                        
                                        <!-- Team Members -->
                                        @foreach($team->members as $member)
                                            <div class="flex items-center space-x-2 bg-slate-50 rounded-lg px-3 py-2">
                                                <x-avatar 
                                                    :src="$member->user->avatar ?? null"
                                                    :name="$member->user->name ?? 'User'"
                                                    size="sm"
                                                    :color="$member->user->avatar_color ?? null" />
                                                <div>
                                                    <p class="text-sm font-medium text-slate-700 flex items-center">{{ $member->user->name }}<x-business-badge :user="$member->user" /></p>
                                                    <p class="text-xs text-slate-500">Member</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- No Teams Message -->
            @if($leadingTeams->count() === 0 && $memberTeams->count() === 0)
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-12 text-center">
                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-team-line text-2xl text-indigo-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-800 mb-2">No teams yet</h3>
                    <p class="text-slate-600 mb-6">Join a team or create your own to participate in hackathons</p>
                    <div class="flex items-center justify-center space-x-4">
                        <a href="{{ route('hackathons.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            <i class="ri-search-line mr-2"></i>
                            Find Teams
                        </a>
                        <a href="{{ route('hackathons.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                            <i class="ri-trophy-line mr-2"></i>
                            Browse Hackathons
                        </a>
                    </div>
                </div>
            @endif
        </div>
        
        <!-- Available Teams Tab -->
        <div x-show="activeTab === 'available-teams'" style="display: none;">
            @if($availableTeams->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 mb-8">
                    <h3 class="text-lg font-semibold text-slate-800 mb-6">Teams Looking for Members</h3>
                    <div class="space-y-4">
                        @foreach($availableTeams as $team)
                            <div class="border border-slate-200 rounded-lg p-6 hover:bg-slate-50 transition-colors">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3 mb-2">
                                            <h4 class="font-semibold text-slate-800">{{ $team->name }}</h4>
                                            <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-600 rounded-full">Open</span>
                                        </div>
                                        <p class="text-sm text-slate-600 mb-3">{{ Str::limit($team->description, 120) }}</p>
                                        <div class="flex items-center space-x-4 text-sm text-slate-500">
                                            <span><i class="ri-trophy-line mr-1"></i>{{ $team->hackathon->title }}</span>
                                            <span><i class="ri-team-line mr-1"></i>{{ $team->member_count }}/{{ $team->hackathon->max_team_size }} members</span>
                                            <span><i class="ri-calendar-line mr-1"></i>{{ $team->hackathon->start_date->format('M d, Y') }}</span>
                                        </div>
                                    </div>
                                    @php
                                        $hasPendingRequest = $sentJoinRequests->contains(function($req) use ($team) {
                                            return $req->team_id === $team->id;
                                        });
                                    @endphp
                                    
                                    @if($hasPendingRequest)
                                        <span class="px-4 py-2 text-sm bg-amber-100 text-amber-700 rounded-lg font-medium">
                                            <i class="ri-time-line mr-1"></i>Request Pending
                                        </span>
                                    @else
                                        <form method="POST" action="{{ route('home.hackathons.teams.join') }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="team_id" value="{{ $team->id }}">
                                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium">
                                                Request to Join
                                            </button>
                                        </form>
                                    @endif
                                </div>
                                
                                <!-- Team Leader -->
                                <div class="border-t border-slate-100 pt-4">
                                    <h5 class="text-sm font-medium text-slate-700 mb-3">Team Leader</h5>
                                    <div class="flex items-center space-x-2">
                                        <x-avatar 
                                            :src="$team->leader->avatar ?? null"
                                            :name="$team->leader->name ?? 'User'"
                                            size="sm"
                                            :color="$team->leader->avatar_color ?? null" />
                                        <div>
                                            <p class="text-sm font-medium text-slate-800 flex items-center">{{ $team->leader->name }}<x-business-badge :user="$team->leader" /></p>
                                            <p class="text-xs text-slate-500">{{ $team->leader->email }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-12 text-center">
                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-team-line text-2xl text-indigo-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-800 mb-2">No available teams</h3>
                    <p class="text-slate-600 mb-6">There are no teams currently looking for members</p>
                </div>
            @endif
        </div>
        
        <!-- Received Invitations Tab -->
        <div x-show="activeTab === 'received-invitations'" style="display: none;">
            @if($receivedInvitations->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 mb-8">
                    <h3 class="text-lg font-semibold text-slate-800 mb-6">Received Invitations</h3>
                    <div class="space-y-4">
                        @foreach($receivedInvitations as $invitation)
                            <div class="border border-indigo-200 rounded-lg p-6 bg-indigo-50">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center space-x-4">
                                        <x-avatar 
                                            :src="$invitation->inviter->avatar ?? null"
                                            :name="$invitation->inviter->name ?? 'User'"
                                            size="md"
                                            :color="$invitation->inviter->avatar_color ?? null" />
                                        <div>
                                            <p class="font-semibold text-slate-800">{{ $invitation->inviter->name }} invited you to join</p>
                                            <p class="text-sm text-slate-600">{{ $invitation->team->name }}</p>
                                            <p class="text-xs text-slate-500">{{ $invitation->team->hackathon->title }}</p>
                                            @if($invitation->message)
                                                <p class="text-sm text-slate-600 mt-2 italic">"{{ $invitation->message }}"</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <form method="POST" action="{{ route('home.hackathons.invitations.accept') }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="invitation_id" value="{{ $invitation->id }}">
                                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">
                                                Accept
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('home.hackathons.invitations.reject') }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="invitation_id" value="{{ $invitation->id }}">
                                            <button type="submit" class="px-4 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 text-sm">
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-12 text-center">
                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-mail-line text-2xl text-indigo-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-800 mb-2">No invitations</h3>
                    <p class="text-slate-600">You don't have any pending invitations</p>
                </div>
            @endif
        </div>
        
        <!-- Received Join Requests Tab -->
        <div x-show="activeTab === 'received-requests'" style="display: none;">
            @if($receivedJoinRequests->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 mb-8">
                    <h3 class="text-lg font-semibold text-slate-800 mb-6">Join Requests for My Teams</h3>
                    <div class="space-y-3">
                        @foreach($receivedJoinRequests as $joinRequest)
                            <div class="border border-blue-100 rounded-xl p-5 bg-gradient-to-r from-blue-50 to-white hover:shadow-md transition-all duration-200">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-start space-x-4 flex-1">
                                        <x-avatar
                                            :src="$joinRequest->user->avatar ?? null"
                                            :name="$joinRequest->user->name ?? 'User'"
                                            size="md"
                                            :color="$joinRequest->user->avatar_color ?? null" />
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-3 mb-2">
                                                <h4 class="font-semibold text-slate-800 text-base flex items-center">{{ $joinRequest->user->name }}<x-business-badge :user="$joinRequest->user" /></h4>
                                                <span class="px-3 py-1 text-xs bg-blue-100 text-blue-700 rounded-full font-semibold">
                                                    <i class="ri-user-add-line mr-1"></i>Join Request
                                                </span>
                                            </div>
                                            <p class="text-sm text-slate-600 mb-1">
                                                <i class="ri-team-line text-blue-600 mr-1"></i>{{ $joinRequest->team->name }}
                                            </p>
                                            <p class="text-sm text-slate-600 mb-2">
                                                <i class="ri-trophy-line text-blue-600 mr-1"></i>{{ $joinRequest->team->hackathon->title }}
                                            </p>
                                            <div class="flex items-center space-x-4 text-xs text-slate-500">
                                                <span><i class="ri-mail-line mr-1"></i>{{ $joinRequest->user->email }}</span>
                                                <span><i class="ri-calendar-line mr-1"></i>{{ $joinRequest->created_at->format('M d, Y') }}</span>
                                                <span>{{ $joinRequest->created_at->diffForHumans() }}</span>
                                            </div>
                                            @if($joinRequest->message)
                                                <div class="mt-3 p-3 bg-blue-50 rounded-lg border border-blue-100">
                                                    <p class="text-sm text-slate-700 italic">"{{ $joinRequest->message }}"</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex space-x-2 ml-4">
                                        <form method="POST" action="{{ route('home.hackathons.join-requests.accept') }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="request_id" value="{{ $joinRequest->id }}">
                                            <button type="submit" class="px-4 py-2 text-sm bg-green-600 text-white hover:bg-green-700 rounded-lg transition-colors font-medium">
                                                <i class="ri-check-line mr-1"></i>Accept
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('home.hackathons.join-requests.reject') }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="request_id" value="{{ $joinRequest->id }}">
                                            <button type="submit" class="px-4 py-2 text-sm bg-red-50 text-red-600 hover:bg-red-100 rounded-lg transition-colors font-medium">
                                                <i class="ri-close-line mr-1"></i>Reject
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-12 text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-user-add-line text-2xl text-blue-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-800 mb-2">No join requests</h3>
                    <p class="text-slate-600">You don't have any pending join requests for your teams</p>
                </div>
            @endif
        </div>
        
        <!-- Sent Join Requests Tab -->
        <div x-show="activeTab === 'sent-requests'" style="display: none;">
            @if($sentJoinRequests->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 mb-8">
                    <h3 class="text-lg font-semibold text-slate-800 mb-6">Sent Join Requests</h3>
                    <div class="space-y-3">
                        @foreach($sentJoinRequests as $request)
                            <div class="border border-amber-100 rounded-xl p-5 bg-gradient-to-r from-amber-50 to-white hover:shadow-md transition-all duration-200">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3 mb-2">
                                            <h4 class="font-semibold text-slate-800 text-base">{{ $request->team->name }}</h4>
                                            <span class="px-3 py-1 text-xs bg-amber-100 text-amber-700 rounded-full font-semibold">
                                                <i class="ri-time-line mr-1"></i>Pending
                                            </span>
                                        </div>
                                        <p class="text-sm text-slate-600 mb-2">
                                            <i class="ri-trophy-line text-amber-600 mr-1"></i>
                                            {{ $request->team->hackathon->title }}
                                        </p>
                                        <div class="flex items-center space-x-4 text-xs text-slate-500">
                                            <span class="flex items-center"><i class="ri-user-line mr-1"></i>{{ $request->team->leader->name }}<x-business-badge :user="$request->team->leader" /></span>
                                            <span><i class="ri-calendar-line mr-1"></i>{{ $request->created_at->format('M d, Y') }}</span>
                                            <span>{{ $request->created_at->diffForHumans() }}</span>
                                        </div>
                                        @if($request->message)
                                            <div class="mt-3 p-3 bg-amber-50 rounded-lg border border-amber-100">
                                                <p class="text-sm text-slate-700 italic">"{{ $request->message }}"</p>
                                            </div>
                                        @endif
                                    </div>
                                    <button type="button" onclick="openCancelRequestModal({{ $request->id }})" 
                                            class="px-4 py-2 text-sm bg-red-50 text-red-600 hover:bg-red-100 rounded-lg transition-colors font-medium">
                                        <i class="ri-close-line mr-1"></i>Cancel
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-12 text-center">
                    <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-user-add-line text-2xl text-amber-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-800 mb-2">No join requests</h3>
                    <p class="text-slate-600">You haven't sent any join requests yet</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Edit Team Modal -->
<div id="editTeamModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-slate-800">Edit Team</h3>
                <button onclick="closeEditTeamModal()" class="text-slate-400 hover:text-slate-600">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>
            
            <form id="editTeamForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="team_id" id="editTeamId">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Team Name</label>
                        <input type="text" name="name" id="editTeamName" 
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Description</label>
                        <textarea name="description" id="editTeamDescription" rows="3"
                                  class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Project Name</label>
                        <input type="text" name="project_name" id="editProjectName" 
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Project Repository</label>
                        <input type="url" name="project_repository" id="editProjectRepo" 
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <input type="checkbox" name="is_public" id="editIsPublic" 
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-slate-300 rounded">
                            <label for="editIsPublic" class="ml-2 text-sm text-slate-700">
                                Public team - Accept join requests from others
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeEditTeamModal()" 
                            class="px-4 py-2 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        Update Team
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Team Modal -->
<div id="createTeamModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-slate-800">Create New Team</h3>
                <button onclick="closeCreateTeamModal()" class="text-slate-400 hover:text-slate-600">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>
            
            <form method="POST" action="{{ route('home.hackathons.teams.create') }}" onsubmit="handleTeamFormSubmit(event)">
                @csrf
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Select Hackathon</label>
                        <select name="hackathon_id" required 
                                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select a hackathon...</option>
                            @foreach(\App\Models\Hackathon::where('is_active', true)
                                ->where('registration_deadline', '>', now())
                                ->where('start_date', '>', now())
                                ->orderBy('registration_deadline')
                                ->get() as $hackathon)
                                <option value="{{ $hackathon->id }}">
                                    {{ $hackathon->title }} 
                                    (Deadline: {{ $hackathon->registration_deadline->format('M d, Y') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Team Name</label>
                        <input type="text" name="name" required 
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Description</label>
                        <textarea name="description" rows="3"
                                  class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <label for="is_public" class="text-sm font-medium text-slate-700">
                            Public team - Accept join requests from others
                        </label>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_public" id="is_public" value="1" checked class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeCreateTeamModal()" 
                            class="px-4 py-2 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        Create Team
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openCreateTeamModal() {
    document.getElementById('createTeamModal').classList.remove('hidden');
}

function closeCreateTeamModal() {
    document.getElementById('createTeamModal').classList.add('hidden');
}

function editTeam(teamId) {
    // Fetch team data and populate modal
    fetch(`/api/teams/${teamId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('editTeamId').value = teamId;
            document.getElementById('editTeamName').value = data.name;
            document.getElementById('editTeamDescription').value = data.description || '';
            document.getElementById('editProjectName').value = data.project_name || '';
            document.getElementById('editProjectRepo').value = data.project_repository || '';
            document.getElementById('editIsPublic').checked = data.is_public;
            
            document.getElementById('editTeamModal').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error fetching team data:', error);
        });
}

function closeEditTeamModal() {
    document.getElementById('editTeamModal').classList.add('hidden');
}

// Member management functions removed - now handled in team-show.blade.php

function handleTeamFormSubmit(event) {
    // Let the form submit normally
    // The page will reload and show success/error message
}

function openCancelRequestModal(requestId) {
    document.getElementById('cancelRequestId').value = requestId;
    const modal = document.getElementById('cancelRequestModal');
    if (modal) {
        document.body.classList.add('overflow-hidden');
        modal.style.display = 'block';
        if (modal.__x && modal.__x.$data) {
            modal.__x.$data.show = true;
        } else {
            setTimeout(() => {
                if (modal.__x && modal.__x.$data) {
                    modal.__x.$data.show = true;
                }
            }, 10);
        }
    }
}

window.openCancelRequestModal = openCancelRequestModal;
</script>
@endpush

<!-- Cancel Request Modal -->
<x-confirm-modal
    id="cancelRequestModal"
    title="Cancel Join Request"
    message="Are you sure you want to cancel this join request?"
    confirmText="Yes, Cancel Request"
    cancelText="Keep Request"
    confirmAction="{{ route('home.hackathons.teams.join-cancel') }}"
    confirmMethod="DELETE"
    danger="true"
>
    <input type="hidden" name="request_id" id="cancelRequestId">
</x-confirm-modal>

@endsection
