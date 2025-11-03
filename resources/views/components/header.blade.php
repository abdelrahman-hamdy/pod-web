<header class="fixed top-0 left-0 right-0 bg-white shadow-sm z-10">
    <div class="w-full px-4 md:px-8 flex items-center justify-between h-16">
        <!-- Mobile Menu Button (leftmost) -->
        <button onclick="toggleMobileSidebar()" class="md:hidden flex items-center justify-center w-10 h-10 text-slate-600 hover:text-indigo-600 transition-colors">
            <i class="ri-menu-line text-xl"></i>
        </button>
        
        <!-- Logo (clickable, floated left on mobile) -->
        <a href="{{ route('home') }}" class="flex items-center space-x-3 hover:opacity-80 transition-opacity">
            <img src="{{ asset('assets/pod-logo.png') }}" alt="People Of Data" class="w-8" />
            <span class="text-xl font-semibold text-slate-800 md:block hidden">People Of Data</span>
        </a>
        
        <!-- Desktop Search Bar -->
        <div class="flex-1 max-w-2xl px-4 md:block hidden">
            <div class="relative" id="search-container">
                <input type="text" 
                       id="search-input" 
                       placeholder="Search posts and users..." 
                       autocomplete="off"
                       class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
                <div class="absolute left-3 top-2.5 text-slate-400 w-4 h-4 flex items-center justify-center pointer-events-none">
                    <i class="ri-search-line"></i>
                </div>
                
                <!-- Live Search Results -->
                <div id="live-search-results" class="absolute top-full left-0 right-0 bg-white border border-slate-200 rounded-lg shadow-lg mt-2 z-50 hidden">
                    <div id="live-search-content" class="max-h-96 overflow-y-auto"></div>
                </div>
            </div>
        </div>
        
        <!-- Mobile Search Icon (floated right) -->
        <button onclick="toggleMobileSearch()" class="md:hidden flex items-center justify-center w-10 h-10 text-slate-600 hover:text-indigo-600 hover:bg-slate-50 rounded-lg transition-all duration-200 ml-auto mr-2">
            <i class="ri-search-line text-lg"></i>
        </button>
        
        <!-- Right Side Actions -->
        <div class="flex items-center space-x-3 md:space-x-6">
            <!-- Messages -->
            <a href="{{ url(config('chatify.routes.prefix')) }}" class="relative text-slate-600 hover:text-indigo-600 transition-colors" id="header-messages-link">
                <div class="w-6 h-6 flex items-center justify-center">
                    <i class="ri-message-3-line"></i>
                </div>
                <span id="header-unread-badge" class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center hidden"></span>
            </a>
            
            <!-- Notifications Panel -->
            <x-notifications-panel />
            
            <!-- Profile Menu -->
            <div class="relative" id="profile-menu">
                <button class="flex items-center md:space-x-3 space-x-0 hover:bg-slate-50 rounded-lg p-2 transition-colors">
                    <x-avatar 
                        :src="auth()->user()->avatar ?? null"
                        :name="auth()->user()->name ?? 'User'"
                        size="sm"
                        :color="auth()->user()->avatar_color ?? null" />
                    <span class="text-sm font-medium text-slate-700 md:block hidden">{{ auth()->user()->name ?? 'User' }}</span>
                    <div class="w-4 h-4 flex items-center justify-center md:block hidden">
                        <i class="ri-arrow-down-s-line"></i>
                    </div>
                </button>
                
                <div class="absolute right-0 top-full mt-2 w-48 bg-white border border-slate-200 rounded-lg shadow-lg py-2 hidden" id="profile-dropdown">
                    <a href="{{ route('profile.show') }}" class="flex items-center space-x-3 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                        <div class="w-4 h-4 flex items-center justify-center">
                            <i class="ri-user-line"></i>
                        </div>
                        <span>Profile</span>
                    </a>
                    <div class="h-px bg-slate-200 my-2"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center space-x-3 px-4 py-2 text-sm text-red-600 hover:bg-slate-50">
                            <div class="w-4 h-4 flex items-center justify-center">
                                <i class="ri-logout-box-line"></i>
                            </div>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Mobile Search Bar -->
    <div id="mobile-search" class="md:hidden hidden bg-white border-t border-slate-200 px-4 py-3">
        <div class="relative">
            <input type="text" 
                   id="mobile-search-input"
                   placeholder="Search posts and users..." 
                   autocomplete="off"
                   class="w-full pl-10 pr-10 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
            <div class="absolute left-3 top-2.5 text-slate-400 w-4 h-4 flex items-center justify-center pointer-events-none">
                <i class="ri-search-line"></i>
            </div>
            <button onclick="closeMobileSearch()" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600">
                <i class="ri-close-line"></i>
            </button>
            
            <!-- Live Search Results Mobile -->
            <div id="mobile-live-search-results" class="absolute top-full left-0 right-0 bg-white border border-slate-200 rounded-lg shadow-lg mt-2 max-h-96 overflow-y-auto z-50 hidden">
                <div id="mobile-live-search-content"></div>
            </div>
        </div>
    </div>

    <script>
        // Live search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-input');
            const mobileSearchInput = document.getElementById('mobile-search-input');
            const liveResults = document.getElementById('live-search-results');
            const mobileLiveResults = document.getElementById('mobile-live-search-results');
            
            let searchTimeout;
            
            // Setup search for desktop
            if (searchInput) {
                searchInput.addEventListener('input', function(e) {
                    const query = e.target.value.trim();
                    clearTimeout(searchTimeout);
                    
                    if (query.length >= 3) {
                        searchTimeout = setTimeout(() => {
                            performLiveSearch(query, 'desktop');
                        }, 300);
                    } else {
                        hideResults('desktop');
                    }
                });
                
                // Handle Enter key
                searchInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        const query = e.target.value.trim();
                        if (query.length >= 3) {
                            window.location.href = `{{ route('search.results') }}?q=${encodeURIComponent(query)}`;
                        }
                    }
                });
                
                // Close results when clicking outside
                document.addEventListener('click', function(e) {
                    if (!e.target.closest('#search-container')) {
                        hideResults('desktop');
                    }
                });
            }
            
            // Setup search for mobile
            if (mobileSearchInput) {
                mobileSearchInput.addEventListener('input', function(e) {
                    const query = e.target.value.trim();
                    clearTimeout(searchTimeout);
                    
                    if (query.length >= 3) {
                        searchTimeout = setTimeout(() => {
                            performLiveSearch(query, 'mobile');
                        }, 300);
                    } else {
                        hideResults('mobile');
                    }
                });
                
                // Handle Enter key
                mobileSearchInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        const query = e.target.value.trim();
                        if (query.length >= 3) {
                            window.location.href = `{{ route('search.results') }}?q=${encodeURIComponent(query)}`;
                        }
                    }
                });
                
                // Close results when clicking outside
                document.addEventListener('click', function(e) {
                    if (!e.target.closest('#mobile-search')) {
                        hideResults('mobile');
                    }
                });
            }
            
            function performLiveSearch(query, type) {
                fetch(`{{ route('search.live') }}?q=${encodeURIComponent(query)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayResults(data, type);
                    }
                })
                .catch(error => {
                    console.error('Search error:', error);
                });
            }
            
            function displayResults(data, type) {
                const resultsDiv = type === 'desktop' 
                    ? document.getElementById('live-search-content')
                    : document.getElementById('mobile-live-search-content');
                
                const resultsContainer = type === 'desktop' 
                    ? liveResults 
                    : mobileLiveResults;
                
                if (!data.posts.length && !data.users.length) {
                    resultsDiv.innerHTML = '<div class="p-6 text-center"><div class="text-slate-400 mb-2"><i class="ri-search-line text-3xl"></i></div><div class="text-sm text-slate-500">No results found</div></div>';
                    resultsContainer.classList.remove('hidden');
                    return;
                }
                
                let html = '';
                
                // Users section
                if (data.users.length > 0) {
                    html += '<div class="px-4 pt-4 pb-2"><div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 flex items-center space-x-2"><i class="ri-user-line"></i><span>People</span></div></div>';
                    data.users.forEach(user => {
                        const initials = user.name ? user.name.substring(0, 2).toUpperCase() : 'U';
                        const avatarColor = user.avatar_color || 'bg-indigo-100 text-indigo-600';
                        const hasAvatar = user.avatar && user.avatar !== '' && user.avatar !== 'null';
                        
                        html += `
                            <a href="/profile/${user.id}" class="flex items-center space-x-3 px-4 py-3 hover:bg-indigo-50 transition-colors group border-b border-slate-100 last:border-b-0">
                                <div class="relative inline-flex items-center justify-center w-10 h-10 text-xs rounded-full ${avatarColor} font-medium overflow-hidden flex-shrink-0 border-2 border-slate-100 group-hover:border-indigo-200 transition-colors">
                                    ${hasAvatar ? `
                                        <img src="${user.avatar}" alt="${user.name || 'User'}" 
                                             class="w-full h-full object-cover rounded-full"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="w-full h-full flex items-center justify-center rounded-full" style="display: none;">
                                            ${initials}
                                        </div>
                                    ` : `
                                        <div class="w-full h-full flex items-center justify-center rounded-full">
                                            ${initials}
                                        </div>
                                    `}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-semibold text-slate-800 group-hover:text-indigo-600 transition-colors flex items-center">${user.name || 'User'}${(user.role === 'client' || user.role === 'admin' || user.role === 'superadmin') ? (() => { const isAdmin = user.role === 'admin' || user.role === 'superadmin'; const tooltip = isAdmin ? 'Administrator' : 'Business Account'; return `<span class="inline-flex items-center justify-center w-4 h-4 bg-emerald-500 rounded-full flex-shrink-0 ml-1.5 badge-group relative" title="${tooltip}" aria-label="${tooltip}" onclick="event.stopPropagation();" onmouseenter="event.stopPropagation();"><i class="ri-check-line text-white text-xs leading-none"></i><span class="badge-tooltip absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-slate-900 text-white text-xs rounded whitespace-nowrap transition-opacity pointer-events-none z-50">${tooltip}<span class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1 border-4 border-transparent border-t-slate-900"></span></span></span>`; })() : ''}</div>
                                    ${user.title ? `<div class="text-xs text-slate-500 line-clamp-1">${user.title}</div>` : ''}
                                </div>
                                <i class="ri-arrow-right-s-line text-slate-300 group-hover:text-indigo-500 transition-colors"></i>
                            </a>
                        `;
                    });
                    html += '<div class="my-2"></div>';
                }
                
                // Posts section
                if (data.posts.length > 0) {
                    html += '<div class="px-4 pt-4 pb-2"><div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 flex items-center space-x-2"><i class="ri-file-text-line"></i><span>Posts</span></div></div>';
                    data.posts.forEach(post => {
                        html += `
                            <a href="/posts/${post.id}" class="block px-4 py-3 hover:bg-indigo-50 transition-colors border-b border-slate-100 last:border-b-0 group">
                                <div class="flex items-start space-x-3">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center flex-shrink-0 group-hover:bg-indigo-100 transition-colors">
                                        <i class="ri-file-text-line text-slate-400 group-hover:text-indigo-600 transition-colors"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm text-slate-700 line-clamp-2 group-hover:text-indigo-900 transition-colors">${post.content || 'No content'}</div>
                                        <div class="text-xs text-slate-500 mt-1.5 flex items-center space-x-2">
                                            <span class="flex items-center space-x-1">
                                                <i class="ri-user-line"></i>
                                                <span class="flex items-center">${post.user ? post.user.name : 'User'}${post.user && (post.user.role === 'client' || post.user.role === 'admin' || post.user.role === 'superadmin') ? (() => { const isAdmin = post.user.role === 'admin' || post.user.role === 'superadmin'; const tooltip = isAdmin ? 'Administrator' : 'Business Account'; return `<span class="inline-flex items-center justify-center w-4 h-4 bg-emerald-500 rounded-full flex-shrink-0 ml-1.5 badge-group relative" title="${tooltip}" aria-label="${tooltip}" onclick="event.stopPropagation();" onmouseenter="event.stopPropagation();"><i class="ri-check-line text-white text-xs leading-none"></i><span class="badge-tooltip absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-slate-900 text-white text-xs rounded whitespace-nowrap transition-opacity pointer-events-none z-50">${tooltip}<span class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1 border-4 border-transparent border-t-slate-900"></span></span></span>`; })() : ''}</span>
                                            </span>
                                            <span>•</span>
                                            <span class="flex items-center space-x-1">
                                                <i class="ri-time-line"></i>
                                                <span>${new Date(post.created_at).toLocaleDateString()}</span>
                                            </span>
                                        </div>
                                    </div>
                                    <i class="ri-arrow-right-s-line text-slate-300 group-hover:text-indigo-500 transition-colors"></i>
                                </div>
                            </a>
                        `;
                    });
                }
                
                // View all results link
                if ((data.posts.length + data.users.length) > 0) {
                    const searchQuery = document.getElementById(type === 'desktop' ? 'search-input' : 'mobile-search-input').value.trim();
                    html += `<div class="px-4 py-3 bg-slate-50 border-t border-slate-200"><a href="{{ route('search.results') }}?q=${encodeURIComponent(searchQuery)}" class="block text-center text-sm font-semibold text-indigo-600 hover:text-indigo-700 transition-colors"><span>View all results</span> <i class="ri-arrow-right-s-line"></i></a></div>`;
                }
                
                resultsDiv.innerHTML = html;
                resultsContainer.classList.remove('hidden');
            }
            
            function hideResults(type) {
                const container = type === 'desktop' ? liveResults : mobileLiveResults;
                container.classList.add('hidden');
            }
        });
    </script>
</header>