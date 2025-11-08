<div x-data="notificationsPanel()" x-init="init()" class="relative inline-block">
    <!-- Notifications Button -->
    <button @click="togglePanel()" class="relative text-slate-600 hover:text-indigo-600 transition-colors">
        <div class="w-6 h-6 flex items-center justify-center">
            <i class="ri-notification-3-line"></i>
        </div>
        <span x-show="unreadCount > 0" x-cloak x-text="unreadCount" 
              class="absolute -top-1 -right-1 min-w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center px-1"></span>
    </button>
    
    <!-- Notifications Panel -->
    <div x-show="isOpen" 
         @click.away="closePanel()"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 top-full mt-2 w-96 bg-white border border-slate-200 rounded-lg shadow-xl z-50 hidden md:block"
         style="max-height: 32rem;">
        <!-- Panel Header -->
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200">
            <h3 class="text-lg font-semibold text-slate-800">Notifications</h3>
            <div class="flex items-center space-x-2">
                <button @click="markAllRead()" x-show="unreadCount > 0" 
                        class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">
                    Mark all read
                </button>
                <button @click="togglePanel()" class="text-slate-400 hover:text-slate-600">
                    <i class="ri-close-line"></i>
                </button>
            </div>
        </div>
        
        <!-- Notifications List -->
        <div class="overflow-y-auto" style="max-height: 26rem;">
            <!-- Loading State -->
            <div x-show="loading" class="p-8 text-center">
                <div class="inline-flex items-center justify-center w-8 h-8 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
                <p class="mt-2 text-sm text-slate-500">Loading...</p>
            </div>
            
            <!-- Empty State -->
            <div x-show="!loading && notifications.length === 0" class="p-8 text-center">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="ri-notification-off-line text-3xl text-slate-400"></i>
                </div>
                <p class="text-sm font-medium text-slate-800">No notifications</p>
                <p class="text-xs text-slate-500 mt-1">You're all caught up!</p>
            </div>
            
            <!-- Notifications -->
            <template x-for="notification in notifications" :key="notification.id">
                <div class="border-b border-slate-100 last:border-b-0 hover:bg-slate-50 transition-colors">
                    <a :href="notification.data.click_action || '#'" 
                       @click.prevent="handleNotificationClick(notification.id, notification.data.click_action)"
                       class="flex items-start px-4 py-3 group block"
                       :class="{ 'bg-indigo-50': !notification.read_at, 'bg-blue-50': notification.read_at && !notification.viewed_at }">
                        <!-- User Avatar with Overlay Icon -->
                        <div class="flex-shrink-0 mr-3 relative">
                            <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-white">
                                <!-- Avatar Image or Initials -->
                                <template x-if="notification.data.actor_avatar && notification.data.actor_avatar !== 'null' && notification.data.actor_avatar !== ''">
                                    <img :src="notification.data.actor_avatar" 
                                         :alt="notification.data.actor_name || 'User'"
                                         class="w-full h-full object-cover">
                                </template>
                                <template x-if="!notification.data.actor_avatar || notification.data.actor_avatar === 'null' || notification.data.actor_avatar === ''">
                                    <div class="w-full h-full flex items-center justify-center font-semibold text-sm"
                                         :class="notification.data.actor_avatar_color || 'bg-slate-100 text-slate-600'">
                                        <span x-text="getInitials(notification.data.actor_name || 'User')"></span>
                                    </div>
                                </template>
                            </div>
                            
                            <!-- Action Icon Overlay -->
                            <div class="absolute -bottom-1.5 -right-1.5 w-6 h-6 rounded-full flex items-center justify-center border-2 border-white"
                                 :class="notification.data.overlay_background_color || 'bg-indigo-500'">
                                <i :class="(notification.data.action_icon || 'ri-notification-3-fill') + ' text-xs ' + (notification.data.icon_color || 'text-white')"></i>
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-slate-900 font-medium line-clamp-2 group-hover:text-indigo-600 transition-colors"
                               x-text="notification.data.body || notification.data.message"></p>
                            <p class="text-xs text-slate-500 mt-1" x-text="formatTime(notification.created_at)"></p>
                        </div>
                        
                        <!-- Read Status Indicator -->
                        <div x-show="!notification.read_at" class="flex-shrink-0 ml-2">
                            <div class="w-2 h-2 bg-indigo-600 rounded-full"></div>
                        </div>
                    </a>
                </div>
            </template>
        </div>
        
        <!-- Panel Footer -->
        <div class="border-t border-slate-200 px-4 py-3">
            <a href="{{ route('notifications.index') }}" class="block text-center text-sm font-medium text-indigo-600 hover:text-indigo-700">
                View all notifications
            </a>
        </div>
    </div>
    
    <!-- Mobile Panel (Full Screen) -->
    <div x-show="isOpen" class="fixed inset-0 bg-white z-50 md:hidden">
        <div class="flex flex-col h-full">
            <!-- Mobile Header -->
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200">
                <h3 class="text-lg font-semibold text-slate-800">Notifications</h3>
                <div class="flex items-center space-x-2">
                    <button @click="markAllRead()" x-show="unreadCount > 0" 
                            class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">
                        Mark all read
                    </button>
                    <button @click="togglePanel()" class="text-slate-400 hover:text-slate-600">
                        <i class="ri-close-line text-xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Mobile Notifications List -->
            <div class="flex-1 overflow-y-auto">
                <!-- Loading/Empty states same as desktop -->
                <div x-show="loading" class="p-8 text-center">
                    <div class="inline-flex items-center justify-center w-8 h-8 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
                    <p class="mt-2 text-sm text-slate-500">Loading...</p>
                </div>
                
                <div x-show="!loading && notifications.length === 0" class="p-8 text-center">
                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="ri-notification-off-line text-3xl text-slate-400"></i>
                    </div>
                    <p class="text-sm font-medium text-slate-800">No notifications</p>
                    <p class="text-xs text-slate-500 mt-1">You're all caught up!</p>
                </div>
                
                <template x-for="notification in notifications" :key="notification.id">
                    <div class="border-b border-slate-100 last:border-b-0 hover:bg-slate-50 transition-colors">
                        <a :href="notification.data.click_action || '#'" 
                           @click.prevent="handleNotificationClick(notification.id, notification.data.click_action)"
                           class="flex items-start px-4 py-3 group block"
                           :class="{ 'bg-indigo-50': !notification.read_at, 'bg-blue-50': notification.read_at && !notification.viewed_at }">
                            <!-- User Avatar with Overlay Icon -->
                            <div class="flex-shrink-0 mr-3 relative">
                                <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-white">
                                    <template x-if="notification.data.actor_avatar && notification.data.actor_avatar !== 'null' && notification.data.actor_avatar !== ''">
                                        <img :src="notification.data.actor_avatar" 
                                             :alt="notification.data.actor_name || 'User'"
                                             class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!notification.data.actor_avatar || notification.data.actor_avatar === 'null' || notification.data.actor_avatar === ''">
                                        <div class="w-full h-full flex items-center justify-center font-semibold text-sm"
                                             :class="notification.data.actor_avatar_color || 'bg-slate-100 text-slate-600'">
                                            <span x-text="getInitials(notification.data.actor_name || 'User')"></span>
                                        </div>
                                    </template>
                                </div>
                                
                                <!-- Action Icon Overlay -->
                                <div class="absolute -bottom-1.5 -right-1.5 w-6 h-6 rounded-full flex items-center justify-center border-2 border-white"
                                     :class="notification.data.overlay_background_color || 'bg-indigo-500'">
                                    <i :class="(notification.data.action_icon || 'ri-notification-3-fill') + ' text-xs ' + (notification.data.icon_color || 'text-white')"></i>
                                </div>
                            </div>
                            
                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-slate-900 font-medium line-clamp-2 group-hover:text-indigo-600 transition-colors"
                                   x-text="notification.data.body || notification.data.message"></p>
                                <p class="text-xs text-slate-500 mt-1" x-text="formatTime(notification.created_at)"></p>
                            </div>
                            
                            <!-- Read Status Indicator -->
                            <div x-show="!notification.read_at" class="flex-shrink-0 ml-2">
                                <div class="w-2 h-2 bg-indigo-600 rounded-full"></div>
                            </div>
                        </a>
                    </div>
                </template>
            </div>
            
            <!-- Mobile Footer -->
            <div class="border-t border-slate-200 px-4 py-3">
                <a href="{{ route('notifications.index') }}" class="block text-center text-sm font-medium text-indigo-600 hover:text-indigo-700">
                    View all notifications
                </a>
            </div>
        </div>
    </div>
    
    <script>
        function notificationsPanel() {
            return {
                isOpen: false,
                loading: false,
                notifications: [],
                unreadCount: 0,
                
                init() {
                    this.loadNotifications();
                    this.loadUnreadCount();
                    
                    // Poll for new notifications every 30 seconds
                    setInterval(() => {
                        this.loadUnreadCount();
                        if (this.isOpen) {
                            this.loadNotifications();
                        }
                    }, 30000);
                },
                
                togglePanel() {
                    this.isOpen = !this.isOpen;
                    if (this.isOpen) {
                        this.loadNotifications();
                        this.markAllAsViewed();
                    }
                },
                
                closePanel() {
                    if (this.isOpen) {
                        this.markAllAsViewed();
                    }
                    this.isOpen = false;
                },
                
                async loadNotifications() {
                    this.loading = true;
                    try {
                        const response = await fetch('/notifications?per_page=10', {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            }
                        });
                        const data = await response.json();
                        this.notifications = data.data || [];
                    } catch (error) {
                        console.error('Failed to load notifications:', error);
                    } finally {
                        this.loading = false;
                    }
                },
                
                async loadUnreadCount() {
                    try {
                        const response = await fetch('/notifications/unread-count', {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            }
                        });
                        const data = await response.json();
                        this.unreadCount = data.data?.count || 0;
                    } catch (error) {
                        console.error('Failed to load unread count:', error);
                    }
                },
                
                async markAllAsViewed() {
                    try {
                        const response = await fetch('/notifications/view-all', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                        });
                        
                        const data = await response.json();
                        
                        // Update unread count from server response
                        this.unreadCount = data.unread_count || 0;
                        
                        // Update local state - mark all as viewed
                        this.notifications.forEach(notification => {
                            if (!notification.viewed_at) {
                                notification.viewed_at = new Date().toISOString();
                            }
                        });
                    } catch (error) {
                        console.error('Failed to mark all as viewed:', error);
                    }
                },
                
                async handleNotificationClick(notificationId, url) {
                    // Mark as read when clicked
                    await this.markAsRead(notificationId);
                    
                    // Navigate to the URL
                    if (url && url !== '#') {
                        window.location.href = url;
                    }
                },
                
                async markAsRead(notificationId) {
                    try {
                        await fetch(`/notifications/${notificationId}/read`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                            },
                        });
                        
                        // Update local state
                        const notification = this.notifications.find(n => n.id === notificationId);
                        if (notification) {
                            notification.read_at = new Date().toISOString();
                            notification.viewed_at = new Date().toISOString();
                        }
                        
                        this.loadUnreadCount();
                    } catch (error) {
                        console.error('Failed to mark notification as read:', error);
                    }
                },
                
                async markAllRead() {
                    try {
                        await fetch('/notifications/read-all', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                        });
                        
                        // Update local state
                        this.notifications.forEach(notification => {
                            notification.read_at = new Date().toISOString();
                            notification.viewed_at = new Date().toISOString();
                        });
                        
                        this.loadUnreadCount();
                    } catch (error) {
                        console.error('Failed to mark all as read:', error);
                    }
                },
                
                formatTime(timestamp) {
                    const date = new Date(timestamp);
                    const now = new Date();
                    const diffInSeconds = Math.floor((now - date) / 1000);
                    
                    if (diffInSeconds < 60) return 'Just now';
                    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
                    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
                    if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)}d ago`;
                    
                    return date.toLocaleDateString();
                },
                
                getInitials(name) {
                    if (!name) return 'U';
                    const parts = name.trim().split(' ');
                    if (parts.length >= 2) {
                        return (parts[0].charAt(0) + parts[1].charAt(0)).toUpperCase();
                    }
                    return name.substring(0, 2).toUpperCase();
                }
            }
        }
    </script>
</div>
