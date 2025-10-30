<nav class="bg-white shadow-lg" x-data="{ open: false }">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between h-16">
            <!-- Logo and main navigation -->
            <div class="flex">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ route('home') }}" class="text-xl font-bold text-primary-600">
                        People Of Data
                    </a>
                </div>

                <!-- Desktop Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <a href="{{ route('home') }}" 
                       class="navbar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('events.index') }}" 
                       class="navbar-link {{ request()->routeIs('events.*') ? 'active' : '' }}">
                        Events
                    </a>
                    <a href="{{ route('jobs.index') }}" 
                       class="navbar-link {{ request()->routeIs('jobs.*') ? 'active' : '' }}">
                        Jobs
                    </a>
                    <a href="{{ route('internships.index') }}"
                       class="navbar-link {{ request()->routeIs('internships.*') ? 'active' : '' }}">
                        Internships
                    </a>
                    <a href="{{ route('hackathons.index') }}" 
                       class="navbar-link {{ request()->routeIs('hackathons.*') ? 'active' : '' }}">
                        Hackathons
                    </a>
                    <a href="{{ route('home') }}" 
                       class="navbar-link {{ request()->routeIs('home') ? 'active' : '' }}">
                        Posts
                    </a>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <div class="relative" x-data="search">
                    <input 
                        type="text" 
                        x-model="query"
                        @input.debounce.300ms="search"
                        placeholder="Search jobs, events, people..."
                        class="w-96 input-field"
                    >
                    <div x-show="results.length > 0" 
                         x-transition
                         class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-lg shadow-lg mt-1 z-50">
                        <template x-for="result in results" :key="result.id">
                            <a :href="result.url" 
                               class="block px-4 py-2 hover:bg-gray-100 border-b border-gray-100 last:border-b-0">
                                <div class="font-medium" x-text="result.title"></div>
                                <div class="text-sm text-gray-600" x-text="result.type"></div>
                            </a>
                        </template>
                    </div>
                </div>
            </div>

            <!-- User Menu -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                @auth
                    <!-- Messages -->
                    <a href="{{ route('chatify') }}" 
                       class="p-2 mr-4 text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-full {{ request()->is('chatify*') ? 'text-primary-600' : '' }}">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </a>

                    <!-- Notifications -->
                    <div class="relative mr-4" x-data="{ open: false }">
                        <button @click="open = !open" 
                                class="p-2 text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-full">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <!-- Notification Badge -->
                            <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
                                3
                            </span>
                        </button>

                        <!-- Notifications Dropdown -->
                        <div x-show="open" 
                             @click.away="open = false"
                             x-transition
                             class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg py-1 z-50">
                            <div class="px-4 py-2 text-sm font-medium text-gray-900 border-b border-gray-200">
                                Notifications
                            </div>
                            <!-- Notification items will be loaded here -->
                            <div class="max-h-96 overflow-y-auto">
                                <a href="#" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-100">
                                    <div class="font-medium">New job application</div>
                                    <div class="text-gray-500">Someone applied for your Data Scientist position</div>
                                    <div class="text-xs text-gray-400 mt-1">2 minutes ago</div>
                                </a>
                                <!-- More notifications... -->
                            </div>
                            <div class="border-t border-gray-200">
                                <a href="{{ route('notifications.index') }}" 
                                   class="block px-4 py-2 text-sm text-center text-primary-600 hover:bg-gray-100">
                                    View all notifications
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- User Dropdown -->
                    <div class="relative ml-3" x-data="{ open: false }">
                        <button @click="open = !open" 
                                class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <x-avatar 
                                :src="auth()->user()->avatar ?? null"
                                :name="auth()->user()->name ?? 'User'"
                                size="sm"
                                :color="auth()->user()->avatar_color ?? null" />
                            <span class="ml-2 text-gray-700">{{ auth()->user()->name }}</span>
                            <svg class="ml-1 h-4 w-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        <div x-show="open" 
                             @click.away="open = false"
                             x-transition
                             class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                            <a href="{{ route('profile.show') }}" 
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Your Profile
                            </a>
                            <a href="{{ route('profile.edit') }}" 
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Settings
                            </a>
                            <a href="{{ route('internships.my-applications') }}"
                                 class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                My Applications
                            </a>
                            @if(auth()->user()->role === 'client')
                                <a href="{{ route('client.dashboard') }}" 
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Client Dashboard
                                </a>
                            @endif
                            @if(in_array(auth()->user()->role, ['admin', 'superadmin']))
                                <a href="{{ route('admin.dashboard') }}" 
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Admin Dashboard
                                </a>
                            @endif
                            <a href="{{ route('chatify') }}" 
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Messages
                            </a>
                            <div class="border-t border-gray-200"></div>
                            <form method="POST" action="{{ route('logout') }}" class="block">
                                @csrf
                                <button type="submit" 
                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Sign out
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <!-- Guest Navigation -->
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('login') }}" class="btn-secondary">
                            Login
                        </a>
                        <a href="{{ route('register') }}" class="btn-primary">
                            Sign Up
                        </a>
                    </div>
                @endauth
            </div>

            <!-- Mobile menu button -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = !open" 
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': !open }" class="inline-flex" 
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{'hidden': !open, 'inline-flex': open }" class="hidden" 
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation Menu -->
    <div :class="{'block': open, 'hidden': !open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <a href="{{ route('home') }}" 
               class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('home') ? 'border-primary-500 text-primary-700 bg-primary-50' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300' }}">
                Home
            </a>
            <a href="{{ route('events.index') }}" 
               class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('events.*') ? 'border-primary-500 text-primary-700 bg-primary-50' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300' }}">
                Events
            </a>
            <a href="{{ route('jobs.index') }}" 
               class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('jobs.*') ? 'border-primary-500 text-primary-700 bg-primary-50' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300' }}">
                Jobs
            </a>
            <a href="{{ route('internships.index') }}"
                class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('internships.*') ? 'border-primary-500 text-primary-700 bg-primary-50' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300' }}">
                Internships
            </a>
            <a href="{{ route('hackathons.index') }}" 
               class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('hackathons.*') ? 'border-primary-500 text-primary-700 bg-primary-50' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300' }}">
                Hackathons
            </a>
            <a href="{{ route('home') }}" 
               class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('home') ? 'border-primary-500 text-primary-700 bg-primary-50' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300' }}">
                Posts
            </a>
            <a href="{{ route('chatify') }}" 
               class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->is('chatify*') ? 'border-primary-500 text-primary-700 bg-primary-50' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300' }}">
                Messages
            </a>
        </div>

        @auth
            <div class="pt-4 pb-1 border-t border-gray-200">
                <div class="flex items-center px-4">
                    <div class="flex-shrink-0">
                        <x-avatar 
                            :src="auth()->user()->avatar ?? null"
                            :name="auth()->user()->name ?? 'User'"
                            size="md"
                            :color="auth()->user()->avatar_color ?? null" />
                    </div>
                    <div class="ml-3">
                        <div class="text-base font-medium text-gray-800">{{ auth()->user()->name }}</div>
                        <div class="text-sm font-medium text-gray-500">{{ auth()->user()->email }}</div>
                    </div>
                </div>

                <div class="mt-3 space-y-1">
                    <a href="{{ route('profile.show') }}" 
                       class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">
                        Your Profile
                    </a>
                    <a href="{{ route('profile.edit') }}" 
                       class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">
                        Settings
                    </a>
                    <a href="{{ route('internships.my-applications') }}"
                        class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">
                        My Applications
                    </a>
                    <a href="{{ route('chatify') }}" 
                       class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">
                        Messages
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="block">
                        @csrf
                        <button type="submit" 
                                class="block w-full text-left px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">
                            Sign out
                        </button>
                    </form>
                </div>
            </div>
        @endauth
    </div>
</nav>
