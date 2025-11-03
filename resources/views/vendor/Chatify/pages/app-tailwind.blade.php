@include('Chatify::layouts.headLinks')

{{-- Main Chat Container - Full Height with Tailwind --}}
<div class="flex h-screen bg-slate-50 overflow-hidden">
    
    {{-- Left Sidebar - Conversations List --}}
    <div class="w-80 bg-white border-r border-slate-200 flex flex-col {{ !!$id ? 'hidden md:flex' : 'flex' }}">
        
        {{-- Sidebar Header --}}
        <div class="p-4 border-b border-slate-200 bg-white">
            <div class="flex items-center justify-between mb-4">
                <a href="{{ route('home') }}" class="flex items-center space-x-3 hover:opacity-80 transition-opacity">
                    <img src="{{ asset('assets/pod-logo.png') }}" alt="People Of Data" class="h-8 w-8 object-contain" />
                    <span class="text-xl font-semibold text-slate-800">People Of Data</span>
                </a>
                <button class="settings-btn text-slate-600 hover:text-indigo-600 transition-colors p-2 rounded-lg hover:bg-slate-100">
                    <i class="fas fa-cog text-lg"></i>
                </button>
            </div>
            
            {{-- Search Input --}}
            <div class="relative">
                <input 
                    type="text" 
                    class="messenger-search w-full px-4 py-2.5 pl-10 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" 
                    placeholder="Search conversations..." />
                <div class="absolute left-3 top-3 text-slate-400">
                    <i class="fas fa-search"></i>
                </div>
            </div>
        </div>

        {{-- Conversations List --}}
        <div class="flex-1 overflow-y-auto contacts-container">
            <div class="show messenger-tab users-tab" data-view="users">
                
                {{-- Favorites Section --}}
                <div class="favorites-section px-2 py-3">
                    <p class="messenger-title px-3 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">Favorites</p>
                    <div class="messenger-favorites space-y-1"></div>
                </div>

                {{-- Saved Messages --}}
                <div class="px-2 py-3 border-t border-slate-100">
                    <p class="messenger-title px-3 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">Your Space</p>
                    {!! view('Chatify::layouts.listItem', ['get' => 'saved']) !!}
                </div>

                {{-- All Conversations --}}
                <div class="px-2 py-3 border-t border-slate-100">
                    <p class="messenger-title px-3 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">All Messages</p>
                    <div class="listOfContacts space-y-1"></div>
                </div>
            </div>

            {{-- Search Results Tab --}}
            <div class="messenger-tab search-tab hidden" data-view="search">
                <div class="px-2 py-3">
                    <p class="messenger-title px-3 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">Search Results</p>
                    <div class="search-records">
                        <p class="message-hint text-center py-8 text-slate-400 text-sm">Type to search...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Chat Area --}}
    <div class="flex-1 flex flex-col messenger-messagingView {{ !!$id ? 'flex' : 'hidden md:flex' }}">
        
        {{-- Chat Header --}}
        <div class="bg-white border-b border-slate-200 px-6 py-4">
            <div class="flex items-center justify-between">
                {{-- Left: Back Button + Contact Info --}}
                <div class="flex items-center space-x-4">
                    <button class="show-listView md:hidden text-slate-600 hover:text-indigo-600 transition-colors p-2 rounded-lg hover:bg-slate-100">
                        <i class="fas fa-arrow-left text-lg"></i>
                    </button>
                    
                    {{-- Contact Widget --}}
                    <div class="header-contact-widget flex items-center space-x-3 cursor-pointer px-3 py-2 rounded-lg hover:bg-slate-50 transition-colors hidden">
                        <div class="header-avatar">
                            <x-chatify-avatar 
                                :src="null"
                                :name="'User'"
                                size="av-m" />
                        </div>
                        <div class="flex flex-col">
                            <span class="user-name font-semibold text-slate-800"></span>
                            <span class="text-xs text-slate-500">Online</span>
                        </div>
                    </div>
                </div>

                {{-- Right: Action Buttons --}}
                <div class="flex items-center space-x-2">
                    <button class="add-to-favorite text-slate-600 hover:text-yellow-500 transition-colors p-2 rounded-lg hover:bg-slate-100" title="Add to favorites">
                        <i class="fas fa-star text-lg"></i>
                    </button>
                    <button class="show-infoSide text-slate-600 hover:text-indigo-600 transition-colors p-2 rounded-lg hover:bg-slate-100" title="User info">
                        <i class="fas fa-info-circle text-lg"></i>
                    </button>
                </div>
            </div>

            {{-- Connection Status --}}
            <div class="internet-connection mt-2">
                <span class="ic-connected inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                    <i class="fas fa-circle text-[6px] mr-2"></i> Connected
                </span>
                <span class="ic-connecting inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
                    <i class="fas fa-circle text-[6px] mr-2 animate-pulse"></i> Connecting...
                </span>
                <span class="ic-noInternet inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
                    <i class="fas fa-circle text-[6px] mr-2"></i> No internet access
                </span>
            </div>
        </div>

        {{-- Messages Area --}}
        <div class="flex-1 overflow-y-auto bg-slate-50 messages-container app-scroll">
            <div class="messages p-6 space-y-4">
                @if (! $id)
                <div class="message-hint flex items-center justify-center h-full">
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-100 rounded-full mb-4">
                            <i class="ri-message-3-line text-3xl text-indigo-600"></i>
                        </div>
                        <p class="text-slate-600 text-lg font-medium">Select a chat to start messaging</p>
                        <p class="text-slate-400 text-sm mt-2">Choose a conversation from the left sidebar</p>
                    </div>
                </div>
                @endif
            </div>

            {{-- Typing Indicator --}}
            <div class="typing-indicator px-6 pb-4 hidden">
                <div class="message-card typing inline-block">
                    <div class="bg-white rounded-2xl px-4 py-3 shadow-sm border border-slate-200">
                        <span class="typing-dots flex space-x-1">
                            <span class="dot dot-1 w-2 h-2 bg-slate-400 rounded-full animate-bounce"></span>
                            <span class="dot dot-2 w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
                            <span class="dot dot-3 w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Send Message Form --}}
        @include('Chatify::layouts.sendForm')
    </div>

    {{-- Right Sidebar - User Info --}}
    <div class="w-80 bg-white border-l border-slate-200 flex-col messenger-infoView hidden">
        {{-- Info Header --}}
        <div class="p-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-800">User Details</h3>
            <button class="text-slate-400 hover:text-slate-600 transition-colors p-2 rounded-lg hover:bg-slate-100">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        
        {{-- Info Content --}}
        <div class="flex-1 overflow-y-auto app-scroll">
            {!! view('Chatify::layouts.info')->render() !!}
        </div>
    </div>
</div>

@include('Chatify::layouts.modals')
@include('Chatify::layouts.footerLinks')
