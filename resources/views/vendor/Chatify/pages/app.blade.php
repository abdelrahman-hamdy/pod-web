@include('Chatify::layouts.headLinks')

{{-- Main Chat Container - Full Height with Tailwind --}}
<div class="flex h-screen bg-slate-50 overflow-hidden">
    
    {{-- Left Sidebar - Conversations List --}}
    <div class="messenger-listView w-80 bg-white border-r border-slate-200 flex flex-col {{ !!$id ? 'conversation-active' : '' }}">
        
        {{-- Logo Section --}}
        <div class="border-b border-slate-200 bg-white">
            <div class="flex items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center space-x-3 hover:opacity-80 transition-opacity">
                    <img src="{{ asset('assets/pod-logo.png') }}" alt="People Of Data" class="h-8 w-8 object-contain" />
                    <span class="text-xl font-semibold text-slate-800">People Of Data</span>
                </a>
            </div>
        </div>
        
        {{-- Search Section --}}
        <div class="px-6 py-4 border-b border-slate-200 bg-white">
            <div class="relative">
                <input 
                    type="text" 
                    class="messenger-search w-full px-4 py-2.5 pl-10 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" 
                    placeholder="Search conversations..." />
                <div class="absolute left-3 top-3 text-slate-400">
                    <i class="ri-search-line"></i>
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
                        <div class="flex items-center justify-center py-12">
                            <div class="text-center">
                                <div class="inline-flex items-center justify-center w-12 h-12 bg-slate-100 rounded-full mb-3">
                                    <i class="ri-search-line text-slate-400 text-lg"></i>
                                </div>
                                <p class="text-slate-400 text-sm font-medium">Nothing to show</p>
                                <p class="text-slate-300 text-xs mt-1">Try searching for a conversation</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Empty State - No Chat Selected --}}
    <div class="flex-1 flex-col items-center justify-center bg-slate-50 {{ !!$id ? 'hidden' : 'flex' }}" id="empty-state-view">
        <div class="text-center px-6">
            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-2xl mb-6">
                <i class="ri-message-3-line text-5xl text-indigo-600"></i>
            </div>
            <h2 class="text-2xl font-bold text-slate-800 mb-3">Select a chat to start messaging</h2>
            <p class="text-slate-500 mb-8 max-w-md">Choose a conversation from the left sidebar or start a new chat with a member</p>
            <div class="flex items-center justify-center space-x-4 text-sm text-slate-400">
                <div class="flex items-center space-x-2">
                    <i class="ri-lock-line"></i>
                    <span>Secure & Private</span>
                </div>
                <div class="flex items-center space-x-2">
                    <i class="ri-shield-check-line"></i>
                    <span>End-to-End Encryption</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Chat Area --}}
    <div class="flex-1 flex-col messenger-messagingView {{ !!$id ? 'flex' : 'hidden' }}" style="{{ !!$id ? 'display: flex;' : 'display: none;' }}">
        
        {{-- Chat Header --}}
        <div class="m-header m-header-messaging bg-white border-b border-slate-200 px-6 py-4">
            <div class="flex items-center justify-between">
                {{-- Left: Back Button + Contact Info --}}
                <div class="flex items-center space-x-4">
                    <button class="show-listView md:hidden text-slate-600 hover:text-indigo-600 transition-colors p-2 rounded-lg hover:bg-slate-100">
                        <i class="ri-arrow-left-line text-lg"></i>
                    </button>
                    
                    {{-- Contact Widget --}}
                    <div class="header-contact-widget flex items-center space-x-3 cursor-pointer px-3 py-2 rounded-lg hover:bg-slate-50 transition-colors hidden">
                        <div class="header-avatar">
                            <div class="relative inline-flex items-center justify-center w-10 h-10 text-sm bg-slate-100 text-slate-600 font-medium avatar" style="overflow: visible !important;">
                                <div class="w-full h-full rounded-full overflow-hidden">
                                    <div class="w-full h-full flex items-center justify-center rounded-full">
                                        <span class="font-medium">--</span>
                                    </div>
                                </div>
                                <span class="absolute -bottom-1 -right-1 w-3 h-3 border-2 border-white rounded-full bg-slate-400" 
                                      title="Offline"
                                      style="z-index: 2; box-shadow: 0 0 0 1px rgba(0,0,0,0.1);"></span>
                            </div>
                        </div>
                        <div class="flex flex-col">
                            <span class="user-name font-semibold text-slate-800"></span>
                            <span class="user-status text-xs text-slate-500">Offline</span>
                        </div>
                    </div>
                </div>

                {{-- Right: Action Buttons --}}
                <div class="flex items-center space-x-2">
                    <button class="add-to-favorite text-slate-600 hover:text-yellow-500 transition-colors p-2 rounded-lg hover:bg-slate-100" title="Add to favorites">
                        <i class="ri-star-line text-xl"></i>
                    </button>
                    <button class="show-infoSide text-slate-600 hover:text-indigo-600 transition-colors p-2 rounded-lg hover:bg-slate-100" title="User info">
                        <i class="ri-information-line text-xl"></i>
                    </button>
                </div>
            </div>

            {{-- Connection Status --}}
            <div class="internet-connection mt-2">
                <span class="ic-connected inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                    <i class="ri-circle-fill text-[6px] mr-2"></i> Connected
                </span>
                <span class="ic-connecting inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
                    <i class="ri-circle-fill text-[6px] mr-2 animate-pulse"></i> Connecting...
                </span>
                <span class="ic-noInternet inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
                    <i class="ri-circle-fill text-[6px] mr-2"></i> No internet access
                </span>
            </div>
        </div>

        {{-- Messages Area --}}
        <div class="m-body flex-1 overflow-y-auto messages-container app-scroll relative">
            {{-- Fixed Background Layers --}}
            <div class="fixed inset-0 bg-gradient-to-br from-blue-500/20 to-indigo-600/20 pointer-events-none" style="top: 73px; left: 320px; right: 0; bottom: 0;"></div>
            <div class="fixed inset-0 opacity-20 pointer-events-none" style="top: 73px; left: 320px; right: 0; bottom: 0; background-image: url('{{ asset('storage/assets/chat-bg-pattern.png') }}'); background-repeat: repeat; background-size: 380px;"></div>
            
            {{-- Content Layer --}}
            <div class="relative z-10">
            <div class="messages p-6 space-y-4">
                <div class="message-hint flex items-center justify-center h-full">
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-100 rounded-full mb-4">
                            <i class="ri-message-3-line text-3xl text-indigo-600"></i>
                        </div>
                        <p class="text-slate-600 text-lg font-medium">Select a chat to start messaging</p>
                        <p class="text-slate-400 text-sm mt-2">Choose a conversation from the left sidebar</p>
                    </div>
                </div>
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

            {{-- Scroll to Bottom Button - Fixed overlay --}}
            <div class="scroll-to-bottom-container fixed bottom-24 z-10 pointer-events-none chat-area-centered">
                <button 
                    id="scroll-to-bottom-btn" 
                    class="scroll-to-bottom-btn w-12 h-12 bg-white hover:bg-slate-50 text-slate-600 hover:text-indigo-600 rounded-full shadow-lg border border-slate-200 flex items-center justify-center transition-all duration-300 opacity-0 translate-y-4 pointer-events-auto"
                    title="Scroll to bottom">
                    <i class="ri-arrow-down-line text-lg"></i>
                </button>
            </div>
        </div>

        {{-- Send Message Form --}}
        @include('Chatify::layouts.sendForm')
    </div>

    {{-- Right Sidebar - User Info --}}
    <div class="w-80 bg-white border-l border-slate-200 flex-col messenger-infoView" style="z-index: 1;">
        {{-- Info Header --}}
        <nav class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-800">User Details</h3>
            <a href="#" class="text-slate-400 hover:text-slate-600 transition-colors p-2 rounded-lg hover:bg-slate-100">
                <i class="ri-close-line text-xl"></i>
            </a>
        </nav>
        
        {{-- Info Content --}}
        <div class="flex-1 overflow-y-auto app-scroll">
            {!! view('Chatify::layouts.info')->render() !!}
        </div>
    </div>
</div>

@include('Chatify::layouts.modals')
@include('Chatify::layouts.footerLinks')
