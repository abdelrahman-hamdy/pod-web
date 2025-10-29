@php
    $isCollapsed = request()->cookie('sidebar_collapsed', 'false') === 'true';
    $sidebarClass = $isCollapsed ? 'sidebar-collapsed' : 'sidebar-expanded';
    $toggleIcon = $isCollapsed ? 'ri-arrow-right-line' : 'ri-menu-line';
@endphp
<aside class="sidebar bg-white shadow-lg fixed h-[calc(100vh-4rem)] z-10 {{ $sidebarClass }}" id="sidebar">
    <!-- Sidebar Toggle Button -->
    <button onclick="toggleSidebar()" id="sidebar-toggle" class="absolute -right-6 top-8 w-6 h-8 bg-white border-t border-r border-b border-slate-200 rounded-tr-sm rounded-br-sm shadow-md flex items-center justify-center text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 z-20">
        <i class="{{ $toggleIcon }} text-sm"></i>
    </button>
    <nav class="p-4">
        <ul class="space-y-2">
            <li>
                <a href="{{ route('home') }}" class="flex items-center px-4 py-3 {{ request()->routeIs('home') ? 'text-indigo-600 bg-indigo-50' : 'text-slate-600' }} hover:text-indigo-600 rounded-lg group transition-colors" title="Home">
                    <div class="w-5 h-5 flex items-center justify-center flex-shrink-0">
                        <i class="ri-home-line"></i>
                    </div>
                    <span class="font-medium sidebar-text ml-3 whitespace-nowrap">Home</span>
                </a>
            </li>
            <li>
                <a href="{{ route('events.index') }}" class="flex items-center px-4 py-3 {{ request()->routeIs('events.*') ? 'text-indigo-600 bg-indigo-50' : 'text-slate-600' }} hover:text-indigo-600 rounded-lg group transition-colors" title="Events">
                    <div class="w-5 h-5 flex items-center justify-center flex-shrink-0">
                        <i class="ri-calendar-event-line"></i>
                    </div>
                    <span class="sidebar-text ml-3 whitespace-nowrap">Events</span>
                </a>
            </li>
            <li>
                <a href="{{ route('jobs.index') }}" class="flex items-center px-4 py-3 {{ request()->routeIs('jobs.*') ? 'text-indigo-600 bg-indigo-50' : 'text-slate-600' }} hover:text-indigo-600 rounded-lg group transition-colors" title="Jobs">
                    <div class="w-5 h-5 flex items-center justify-center flex-shrink-0">
                        <i class="ri-briefcase-line"></i>
                    </div>
                    <span class="sidebar-text ml-3 whitespace-nowrap">Jobs</span>
                </a>
            </li>
            <li>
                <a href="{{ route('hackathons.index') }}" class="flex items-center px-4 py-3 {{ request()->routeIs('hackathons.index') || request()->routeIs('hackathons.show') || request()->routeIs('hackathons.create') || request()->routeIs('hackathons.edit') ? 'text-indigo-600 bg-indigo-50' : 'text-slate-600' }} hover:text-indigo-600 rounded-lg group transition-colors" title="Browse Hackathons">
                    <div class="w-5 h-5 flex items-center justify-center flex-shrink-0">
                        <i class="ri-trophy-line"></i>
                    </div>
                    <span class="sidebar-text ml-3 whitespace-nowrap">Hackathons</span>
                </a>
            </li>
            <li>
                <a href="{{ route('home.hackathons.teams') }}" class="flex items-center px-4 py-3 {{ request()->routeIs('home.hackathons.teams') ? 'text-indigo-600 bg-indigo-50' : 'text-slate-600' }} hover:text-indigo-600 rounded-lg group transition-colors" title="My Teams">
                    <div class="w-5 h-5 flex items-center justify-center flex-shrink-0">
                        <i class="ri-team-line"></i>
                    </div>
                    <span class="sidebar-text ml-3 whitespace-nowrap">My Teams</span>
                </a>
            </li>
            <li>
                <a href="{{ route('solutions.index') }}" class="flex items-center px-4 py-3 {{ request()->routeIs('solutions.index') ? 'text-indigo-600 bg-indigo-50' : 'text-slate-600' }} hover:text-indigo-600 rounded-lg group transition-colors" title="Solutions">
                    <div class="w-5 h-5 flex items-center justify-center flex-shrink-0">
                        <i class="ri-lightbulb-line"></i>
                    </div>
                    <span class="sidebar-text ml-3 whitespace-nowrap">Solutions</span>
                </a>
            </li>
            <li>
                <a href="{{ url(config('chatify.routes.prefix')) }}" class="flex items-center px-4 py-3 {{ request()->is(config('chatify.routes.prefix').'*') ? 'text-indigo-600 bg-indigo-50' : 'text-slate-600' }} hover:text-indigo-600 rounded-lg group relative transition-colors" title="Messages" id="messages-link">
                    <div class="w-5 h-5 flex items-center justify-center flex-shrink-0">
                        <i class="ri-message-3-line"></i>
                    </div>
                    <span class="sidebar-text ml-3 whitespace-nowrap">Messages</span>
                    {{-- Unread Conversations Badge --}}
                    <span id="unread-conversations-badge" class="hidden ml-auto inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform rounded-full bg-red-500 transition-all duration-200" style="min-width: 1.5rem; height: 1.5rem;">0</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>