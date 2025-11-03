<script src="https://js.pusher.com/7.2.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@joeattardi/emoji-button@2.8.2/dist/index.min.js"></script>
<script>
    // Global Chatify variables from PHP to JS
    window.chatify = {
        name: "{{ config('chatify.name') }}",
        sounds: {!! json_encode(config('chatify.sounds')) !!},
        allowedImages: {!! json_encode(config('chatify.attachments.allowed_images')) !!},
        allowedFiles: {!! json_encode(config('chatify.attachments.allowed_files')) !!},
        maxUploadSize: {{ app('ChatifyMessenger')->getMaxUploadSize() }},
        pusher: {!! json_encode(config('chatify.pusher')) !!},
        pusherAuthEndpoint: '{{route("pusher.auth")}}'
    };
    window.chatify.allAllowedExtensions = chatify.allowedImages.concat(chatify.allowedFiles);
</script>
<script src="{{ asset('js/chatify/utils.js') }}"></script>
<script src="{{ asset('js/chatify/code.js') }}?v={{ time() }}"></script>

<script>
// Custom functionality for People Of Data chat
document.addEventListener('DOMContentLoaded', function() {
    let currentContactId = null;
    
    // Handle empty state visibility
    function toggleEmptyState(show) {
        const emptyState = document.getElementById('empty-state-view');
        const messagingView = document.querySelector('.messenger-messagingView');
        
        if (emptyState && messagingView) {
            if (show) {
                emptyState.classList.remove('hidden');
                emptyState.classList.add('flex');
                messagingView.classList.add('hidden');
                messagingView.classList.remove('flex');
                messagingView.style.display = 'none';
            } else {
                emptyState.classList.add('hidden');
                emptyState.classList.remove('flex');
                messagingView.classList.remove('hidden');
                messagingView.classList.add('flex');
                messagingView.style.display = 'flex';
            }
        }
    }
    
    // Store original IDinfo for later override (will be overridden once after all functions are defined)
    let storedOriginalIDinfo = null;
    
    // Animated Notification System
    function showNotification(message, type = 'success', duration = 2000) {
        const container = document.getElementById('notification-container');
        if (!container) return;
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        
        // Create icon based on type
        let iconHtml = '';
        switch(type) {
            case 'success':
                iconHtml = '<div class="notification-icon success"><i class="ri-check-line text-xs"></i></div>';
                break;
            case 'warning':
                iconHtml = '<div class="notification-icon warning"><i class="ri-error-warning-line text-xs"></i></div>';
                break;
            case 'error':
                iconHtml = '<div class="notification-icon error"><i class="ri-close-line text-xs"></i></div>';
                break;
            default:
                iconHtml = '<div class="notification-icon success"><i class="ri-information-line text-xs"></i></div>';
        }
        
        notification.innerHTML = `${iconHtml}<span>${message}</span>`;
        
        // Add to container
        container.appendChild(notification);
        
        // Trigger animation
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        // Auto remove after duration
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, duration);
    }
    
    // Make notification function globally available
    window.showNotification = showNotification;
    
    // Override Chatify's checkInternet function to use our notifications
    if (typeof window.checkInternet === 'function') {
        const originalCheckInternet = window.checkInternet;
        window.checkInternet = function(state, selector) {
            // Call original function but hide the result
            originalCheckInternet(state, selector);
            
            // Show our notification instead
            switch(state) {
                case 'connected':
                    showNotification('Connected', 'success', 2000);
                    break;
                case 'connecting':
                    showNotification('Connecting...', 'warning', 3000);
                    break;
                default:
                    showNotification('No internet access', 'error', 3000);
                    break;
            }
        };
    }
    
    // Check if there's an ID in the URL and load the chat
    function checkAndLoadInitialChat() {
        const metaId = document.querySelector('meta[name="id"]');
        if (metaId && metaId.content && metaId.content !== '0') {
            console.log('Found initial chat ID:', metaId.content);
            // Wait for Chatify to be ready, then load the chat
            setTimeout(() => {
                if (typeof window.IDinfo === 'function') {
                    console.log('Loading initial chat for ID:', metaId.content);
                    window.IDinfo(metaId.content);
                } else {
                    // If IDinfo is not ready yet, try again
                    setTimeout(checkAndLoadInitialChat, 500);
                }
            }, 1000);
        }
    }
    
    // Check for initial chat after a short delay to ensure Chatify is ready
    setTimeout(checkAndLoadInitialChat, 500);
    
    // Make header contact clickable
    function makeHeaderContactClickable() {
        // Add click handler to the entire contact widget
        document.addEventListener('click', function(e) {
            const contactWidget = e.target.closest('.header-contact-widget');
            
                    if (contactWidget) {
                        // Get contact ID from data attribute or currentContactId variable
                        const contactId = contactWidget.getAttribute('data-contact-id') || currentContactId;

                        // Only navigate to profile if it's not saved messages (current user)
                        if (contactId && contactId !== '{{ Auth::id() }}') {
                            e.preventDefault();
                            e.stopPropagation();
                            console.log('Navigating to profile:', contactId);
                            window.location.href = `/profile/${contactId}`;
                        } else if (contactId === '{{ Auth::id() }}') {
                            // For saved messages, don't navigate anywhere
                            e.preventDefault();
                            e.stopPropagation();
                            console.log('Saved messages - not navigating');
                        }
                    }
        });
    }
    
    // Track when a contact is selected
    function trackContactSelection() {
        // Override Chatify's contact selection
        const originalOpenChat = window.openChat;
        if (originalOpenChat) {
            window.openChat = function(contactId) {
                currentContactId = contactId;
                console.log('Contact selected:', contactId);
                return originalOpenChat.apply(this, arguments);
            };
        }
        
        // Also listen for contact list clicks
        document.addEventListener('click', function(e) {
            const contactItem = e.target.closest('.messenger-list-item');
            if (contactItem) {
                const contactId = contactItem.getAttribute('data-contact');
                if (contactId) {
                    currentContactId = contactId;
                    console.log('Contact clicked:', contactId);
                }
            }
        });
    }
    
    // Helper function to generate consistent color based on name (matching PHP chatify-avatar component)
    function getAvatarColor(name) {
        // Use default fallback color - actual colors come from database
        return 'bg-slate-100 text-slate-600';
    }
    
    // Master function to update header avatar - prevents conflicts
    function updateHeaderAvatar(userName, userAvatar, isOnline, avatarColor = 'bg-slate-100 text-slate-600') {
        console.log('=== MASTER AVATAR UPDATE ===');
        console.log('Name:', userName, 'Avatar:', userAvatar, 'Online:', isOnline, 'Color:', avatarColor);
        console.log('isOnline type:', typeof isOnline, 'value:', isOnline);
        console.log('Status will be:', isOnline ? 'Online (green)' : 'Offline (gray)');
        
        const headerAvatar = document.querySelector('.header-avatar .avatar');
        const userStatus = document.querySelector('.user-status');
        const userNameElement = document.querySelector('.user-name');
        
        if (!headerAvatar) {
            console.log('Header avatar not found!');
            return;
        }
        
        const initials = userName.substring(0, 2).toUpperCase();
        
        // Update avatar container with provided color
        headerAvatar.className = `relative inline-flex items-center justify-center w-10 h-10 text-sm ${avatarColor} font-medium avatar`;
        headerAvatar.style.overflow = 'visible !important';
        
        // Update avatar content
        if (userAvatar && userAvatar !== '' && userAvatar !== 'null') {
            headerAvatar.innerHTML = `
                <div class="w-full h-full rounded-full overflow-hidden">
                    <img src="${userAvatar}" alt="${userName}" class="w-full h-full object-cover rounded-full" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="w-full h-full flex items-center justify-center rounded-full" style="display: none;">
                        <span class="font-medium">${initials}</span>
                    </div>
                </div>
                <span class="absolute -bottom-1 -right-1 w-3 h-3 border-2 border-white rounded-full ${isOnline ? 'bg-green-500' : 'bg-slate-400'}" 
                      title="${isOnline ? 'Online' : 'Offline'}"
                      style="z-index: 2; box-shadow: 0 0 0 1px rgba(0,0,0,0.1);"></span>
            `;
        } else {
            headerAvatar.innerHTML = `
                <div class="w-full h-full rounded-full overflow-hidden">
                    <div class="w-full h-full flex items-center justify-center rounded-full">
                        <span class="font-medium">${initials}</span>
                    </div>
                </div>
                <span class="absolute -bottom-1 -right-1 w-3 h-3 border-2 border-white rounded-full ${isOnline ? 'bg-green-500' : 'bg-slate-400'}" 
                      title="${isOnline ? 'Online' : 'Offline'}"
                      style="z-index: 2; box-shadow: 0 0 0 1px rgba(0,0,0,0.1);"></span>
            `;
        }
        
        // Update user name and status
        if (userNameElement) userNameElement.textContent = userName;
        if (userStatus) {
            userStatus.textContent = isOnline ? 'Online' : 'Offline';
            console.log('Updated status text to:', userStatus.textContent);
        }
        
        console.log('✅ Avatar updated successfully');
        console.log('=== END MASTER AVATAR UPDATE ===');
    }
    
    // Helper function to update info sidebar avatar
    function updateInfoSidebar(contactName, avatarSrc, initials, avatarColor) {
        const infoAvatar = document.querySelector('.messenger-infoView .avatar');
        const infoName = document.querySelector('.info-name');
        
        if (infoAvatar && infoName) {
            // Remove existing color classes and add new ones
            infoAvatar.className = infoAvatar.className.replace(/bg-\w+-\d+|text-\w+-\d+/g, '').trim();
            infoAvatar.className += ` ${avatarColor}`;
            
            // Ensure the container is rounded and has no background image
            infoAvatar.style.backgroundImage = 'none';
            infoAvatar.style.backgroundSize = 'unset';
            infoAvatar.style.backgroundPosition = 'unset';
            infoAvatar.style.borderRadius = '50%';
            infoAvatar.style.overflow = 'hidden';
            
            if (avatarSrc) {
                infoAvatar.innerHTML = `<img src="${avatarSrc}" alt="${contactName}" class="w-full h-full object-cover rounded-full" onerror="this.style.display='none'; this.parentElement.innerHTML='<span class=\\'font-medium\\'>${initials}</span>';">`;
            } else {
                infoAvatar.innerHTML = `<span class="font-medium">${initials}</span>`;
            }
            infoName.textContent = contactName;
        }
    }
    
    // Update header when contact changes
    function updateHeaderContact(contactId, contactName, contactAvatar) {
        console.log('=== updateHeaderContact CALLED ===');
        console.log('contactId:', contactId, 'contactName:', contactName, 'contactAvatar:', contactAvatar);
        
        const contactWidget = document.querySelector('.header-contact-widget');
        
        if (contactId) {
            // Show the contact widget and store contact ID
            if (contactWidget) {
                contactWidget.classList.remove('hidden');
                contactWidget.classList.add('flex');
                contactWidget.setAttribute('data-contact-id', contactId);
                console.log('Set data-contact-id to:', contactId);
            }
            
            // For saved messages (current user), get the current user's avatar
            if (contactId === '{{ Auth::id() }}') {
                // Get current user's avatar from the page
                const currentUserAvatar = '{{ Auth::user()->avatar ?? "" }}';
                contactAvatar = currentUserAvatar;
                console.log('Saved messages - using current user avatar:', contactAvatar);
            }
            
            // Process avatar URL - Chatify already returns full URL
            let avatarSrc = contactAvatar && contactAvatar !== '' ? contactAvatar : null;
            const initials = contactName.substring(0, 2).toUpperCase();
            
            // Use master function to update header avatar with actual status
            // Fetch user status to get accurate online/offline status
            fetch(`{{ url(config("chatify.routes.prefix")) }}/idInfo`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ id: contactId })
            })
            .then(response => response.json())
            .then(data => {
                console.log('updateHeaderContact - API response:', data);
                if (data && data.fetch) {
                    const isOnline = data.fetch.active_status === 1 || data.fetch.active_status === true;
                    const avatarColor = data.fetch.avatar_color || 'bg-slate-100 text-slate-600';
                    console.log('updateHeaderContact - active_status:', data.fetch.active_status, 'isOnline:', isOnline);
                    console.log('updateHeaderContact - avatar_color:', avatarColor);
                    updateHeaderAvatar(contactName, avatarSrc, isOnline, avatarColor);
                    updateInfoSidebar(contactName, avatarSrc, initials, avatarColor);
                    console.log('✅ Updated avatar with actual status:', isOnline);
                } else {
                    // Fallback to offline if no data
                    updateHeaderAvatar(contactName, avatarSrc, false, 'bg-slate-100 text-slate-600');
                    updateInfoSidebar(contactName, avatarSrc, initials, 'bg-slate-100 text-slate-600');
                    console.log('⚠️ No status data, defaulting to offline');
                }
            })
            .catch(error => {
                console.log('Error fetching user status:', error);
                // Fallback to offline on error
                updateHeaderAvatar(contactName, avatarSrc, false, 'bg-slate-100 text-slate-600');
                updateInfoSidebar(contactName, avatarSrc, initials, 'bg-slate-100 text-slate-600');
            });
            
            // Update header name
            const userName = document.querySelector('.user-name');
            if (userName) {
                userName.textContent = contactName;
            }
            
            // Show delete button in info sidebar (but not for saved messages)
            const deleteBtn = document.querySelector('.delete-conversation');
            if (deleteBtn) {
                if (contactId !== '{{ Auth::id() }}') {
                    deleteBtn.classList.remove('hidden');
                } else {
                    deleteBtn.classList.add('hidden');
                }
            }

            // Check and update favorite button state
            checkAndUpdateFavoriteButton(contactId);
            
            // Load shared photos for this contact
            loadSharedPhotos(contactId);
        } else {
            // Hide the contact widget when no conversation is selected
            if (contactWidget) {
                contactWidget.classList.add('hidden');
                contactWidget.classList.remove('flex');
                contactWidget.removeAttribute('data-contact-id');
            }
            
            // Clear shared photos when no contact is selected
            clearSharedPhotos();
        }
    }
    
    // Load shared photos for a contact
    function loadSharedPhotos(contactId) {
        if (!contactId) {
            clearSharedPhotos();
            return;
        }
        
        $.ajax({
            url: '{{ route("shared") }}',
            method: "POST",
            data: { 
                _token: '{{ csrf_token() }}', 
                user_id: contactId 
            },
            dataType: "JSON",
            success: function(data) {
                if (data.shared) {
                    $(".shared-photos-list").html(data.shared);
                } else {
                    clearSharedPhotos();
                }
            },
            error: function() {
                console.error("Error loading shared photos");
                clearSharedPhotos();
            }
        });
    }
    
    // Clear shared photos display
    function clearSharedPhotos() {
        $(".shared-photos-list").html('<p class="col-span-3 text-center text-slate-400 text-sm py-4">No shared photos yet</p>');
    }
    
    // Handle shared photo clicks for lightbox
    $(document).on('click', '.shared-photo img', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const imageSrc = $(this).attr('src');
        if (imageSrc) {
            showUnifiedLightbox(imageSrc);
        }
    });
    
    // Hook into Chatify's contact loading
    function hookContactLoading() {
        const originalLoadContact = window.loadContact;
        if (originalLoadContact) {
            window.loadContact = function(contactId) {
                const result = originalLoadContact.apply(this, arguments);
                currentContactId = contactId;

                // Get contact info and update header
                fetch(`/chat/api/idInfo`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ id: contactId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.user) {
                        updateHeaderContact(contactId, data.user.name, data.user.avatar);
                    }
                })
                .catch(error => console.log('Error loading contact info:', error));

                return result;
            };
        }
    }
    
    // This override will be consolidated at the end
    
    // Simple function to check if contact is favorited and update button
    function checkAndUpdateFavoriteButton(contactId) {
        console.log('=== STAR BUTTON DEBUG ===');
        console.log('Contact ID:', contactId);
        
        if (contactId && contactId !== '{{ Auth::id() }}') {
            $('.add-to-favorite').show();
            
            // Simple check: look for the contact in favorites section
            const favoriteItem = $(`.favorite-list-item[data-contact="${contactId}"]`);
            const isFavorite = favoriteItem.length > 0;
            
            console.log('Favorite item found:', favoriteItem.length);
            console.log('Is favorite:', isFavorite);
            
            if (isFavorite) {
                $('.add-to-favorite').addClass('favorite');
                // Remove ALL color classes from button AND icon, change to solid star
                $('.add-to-favorite').removeClass('text-slate-600 hover:text-yellow-500').addClass('text-yellow-500');
                $('.add-to-favorite i').removeClass('text-slate-600 text-yellow-500 ri-star-line').addClass('text-yellow-500 ri-star-fill');
                $('.add-to-favorite').attr('title', 'Remove from favorites');
                console.log('✅ SET TO YELLOW SOLID - Remove from favorites');
                console.log('Button classes after:', $('.add-to-favorite').attr('class'));
                console.log('Icon classes after:', $('.add-to-favorite i').attr('class'));
            } else {
                $('.add-to-favorite').removeClass('favorite');
                // Remove ALL color classes from button AND icon, change to linear star
                $('.add-to-favorite').removeClass('text-yellow-500').addClass('text-slate-600 hover:text-yellow-500');
                $('.add-to-favorite i').removeClass('text-slate-600 text-yellow-500 ri-star-fill').addClass('text-slate-600 ri-star-line');
                $('.add-to-favorite').attr('title', 'Add to favorites');
                console.log('❌ SET TO GRAY LINEAR - Add to favorites');
                console.log('Button classes after:', $('.add-to-favorite').attr('class'));
                console.log('Icon classes after:', $('.add-to-favorite i').attr('class'));
            }
            
            console.log('Final classes:', $('.add-to-favorite').attr('class'));
            console.log('Final color:', $('.add-to-favorite i').css('color'));
            console.log('=== END DEBUG ===');
        } else {
            $('.add-to-favorite').hide();
        }
    }
    
    // Override Chatify's star function completely to prevent duplication
    window.originalStar = window.star;
    window.star = function(user_id) {
        console.log('Custom star function called with user_id:', user_id);
        // Don't call originalStar to prevent duplication
        $.ajax({
            url: '{{ route("star") }}',
            method: "POST",
            data: { _token: '{{ csrf_token() }}', user_id: user_id },
            dataType: "JSON",
            success: (data) => {
                console.log('Star click response:', data);
                
                // Update star button immediately
                if (data.status > 0) {
                    $(".add-to-favorite").addClass("favorite");
                    $(".add-to-favorite").removeClass('text-slate-600 hover:text-yellow-500').addClass('text-yellow-500');
                    $(".add-to-favorite i").removeClass('text-slate-600 text-yellow-500 ri-star-line').addClass('text-yellow-500 ri-star-fill');
                    $(".add-to-favorite").attr('title', 'Remove from favorites');
                    console.log('✅ CLICKED: SET TO YELLOW SOLID - Remove from favorites');
                } else {
                    $(".add-to-favorite").removeClass("favorite");
                    $(".add-to-favorite").removeClass('text-yellow-500').addClass('text-slate-600 hover:text-yellow-500');
                    $(".add-to-favorite i").removeClass('text-slate-600 text-yellow-500 ri-star-fill').addClass('text-slate-600 ri-star-line');
                    $(".add-to-favorite").attr('title', 'Add to favorites');
                    console.log('❌ CLICKED: SET TO GRAY LINEAR - Add to favorites');
                }

                // Reload favorites list
                if (typeof getFavoritesList === 'function') {
                    getFavoritesList();
                }
            },
            error: (xhr, status, error) => {
                console.error("Error toggling favorite:", xhr.responseText);
                console.error("Status:", status);
                console.error("Error:", error);
            },
        });
    };
    
    // Completely disable the original star function to prevent any conflicts
    window.star = function(user_id) {
        console.log('Original star function disabled - preventing call');
        return false;
    };
    
    // Use a flag to prevent duplicate calls
    let starButtonClicked = false;
    
    // Override the click handler completely with multiple strategies
    function bindCustomStarHandler() {
        // Remove ALL existing handlers
        $('body').off('click', '.add-to-favorite');
        $(document).off('click', '.add-to-favorite');
        $('.add-to-favorite').off('click');
        
        // Bind our custom handler
        $('body').on('click', '.add-to-favorite', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            // Prevent duplicate calls
            if (starButtonClicked) {
                console.log('Star button already clicked, ignoring duplicate');
                return false;
            }
            
            starButtonClicked = true;
            console.log('Custom click handler triggered - making direct AJAX call');
            
            // Make the AJAX call directly
            $.ajax({
                url: '{{ route("star") }}',
                method: "POST",
                data: { _token: '{{ csrf_token() }}', user_id: getMessengerId() },
                dataType: "JSON",
                success: (data) => {
                    console.log('Direct AJAX star response:', data);
                    
                    // Update star button immediately
                    if (data.status > 0) {
                        $(".add-to-favorite").addClass("favorite");
                        $(".add-to-favorite").removeClass('text-slate-600 hover:text-yellow-500').addClass('text-yellow-500');
                        $(".add-to-favorite i").removeClass('text-slate-600 text-yellow-500 ri-star-line').addClass('text-yellow-500 ri-star-fill');
                        $(".add-to-favorite").attr('title', 'Remove from favorites');
                        console.log('✅ DIRECT AJAX: SET TO YELLOW SOLID - Remove from favorites');
                    } else {
                        $(".add-to-favorite").removeClass("favorite");
                        $(".add-to-favorite").removeClass('text-yellow-500').addClass('text-slate-600 hover:text-yellow-500');
                        $(".add-to-favorite i").removeClass('text-slate-600 text-yellow-500 ri-star-fill').addClass('text-slate-600 ri-star-line');
                        $(".add-to-favorite").attr('title', 'Add to favorites');
                        console.log('❌ DIRECT AJAX: SET TO GRAY LINEAR - Add to favorites');
                    }

                    // Reload favorites list
                    if (typeof getFavoritesList === 'function') {
                        getFavoritesList();
                    }
                    
                    // Reset flag after successful request
                    setTimeout(() => {
                        starButtonClicked = false;
                    }, 1000);
                },
                error: (xhr, status, error) => {
                    console.error("Direct AJAX error:", xhr.responseText);
                    // Reset flag on error
                    starButtonClicked = false;
                },
            });
            
            return false;
        });
    }
    
    // Bind immediately
    bindCustomStarHandler();
    
    // Re-bind after delays to override any handlers that get added later
    setTimeout(bindCustomStarHandler, 500);
    setTimeout(bindCustomStarHandler, 1000);
    setTimeout(bindCustomStarHandler, 2000);
    
    // Override search behavior - only hide chats when typing, not on focus
    $('.messenger-search').off('focus').on('focus', function() {
        // Don't hide chats on focus - let user see them while typing
        console.log('Search focused - keeping chats visible');
    });
    
    // Override the keyup behavior to show search results only when typing
    $('.messenger-search').off('keyup').on('keyup', function(e) {
        const value = $(this).val();
        if ($.trim(value).length > 0) {
            // Only hide chats and show search when user is actually typing
            $(".messenger-tab").hide();
            $('.messenger-tab[data-view="search"]').show();
            
            // Trigger search with debounce
            clearTimeout(window.searchTimeout);
            window.searchTimeout = setTimeout(function() {
                if (typeof messengerSearch === 'function') {
                    messengerSearch(value);
                }
            }, 300);
        } else {
            // Show chats again when search is empty
            $(".messenger-tab").hide();
            $('.messenger-tab[data-view="users"]').show();
        }
    });
    
    // Override blur behavior to show chats when search loses focus
    $('.messenger-search').off('blur').on('blur', function() {
        setTimeout(function() {
            $(".messenger-tab").hide();
            $('.messenger-tab[data-view="users"]').show();
        }, 200);
    });
    
    // Initialize everything
    makeHeaderContactClickable();
    trackContactSelection();
    hookContactLoading();
    
    // Unified lightbox functionality for all images
    function showUnifiedLightbox(imageSrc) {
        // Remove any existing lightbox
        const existingLightbox = document.getElementById('unified-lightbox');
        if (existingLightbox) {
            existingLightbox.remove();
        }
        
        // Create unified lightbox
        const lightbox = document.createElement('div');
        lightbox.id = 'unified-lightbox';
        lightbox.className = 'fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-50 cursor-pointer';
        lightbox.innerHTML = `
            <div class="p-16 w-full h-full flex items-center justify-center">
                <img src="${imageSrc}" alt="Image" class="max-w-full max-h-full object-contain" style="border-radius: 0;">
            </div>
        `;
        
        document.body.appendChild(lightbox);
        
        // Close lightbox on click outside image
        lightbox.addEventListener('click', function(e) {
            // Only close if clicking on the background or padding area, not the image
            if (e.target === lightbox || e.target.classList.contains('p-16')) {
                lightbox.remove();
            }
        });
        
        // Prevent image click from closing lightbox
        const image = lightbox.querySelector('img');
        image.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }

    // Add click handlers to chat images
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('chat-image')) {
            e.preventDefault();
            e.stopPropagation();
            const imageSrc = e.target.getAttribute('src') || e.target.getAttribute('data-image');
            if (imageSrc) {
                showUnifiedLightbox(imageSrc);
            }
        }
    });

    // Keep the old function names for compatibility
    window.openLightbox = function(imageSrc) {
        showUnifiedLightbox(imageSrc);
    };

    window.closeLightbox = function() {
        const lightbox = document.getElementById('unified-lightbox');
        if (lightbox) {
            lightbox.remove();
        }
    };

    // Close lightbox on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeLightbox();
        }
    });

    // Close lightbox on background click
    document.addEventListener('click', function(e) {
        if (e.target.id === 'image-lightbox') {
            closeLightbox();
        }
    });

    // Override emoji picker theme to always use light mode
    setTimeout(() => {
        // Try multiple approaches to override the theme
        if (typeof emojiPicker !== 'undefined' && emojiPicker) {
            console.log('Overriding emoji picker theme to light mode');
            emojiPicker.theme = 'light';
            
            // Force re-render with light theme
            if (emojiPicker.picker) {
                emojiPicker.picker.classList.remove('dark');
                emojiPicker.picker.classList.add('light');
            }
        }
        
        // Also override the global messengerTheme variable
        if (typeof messengerTheme !== 'undefined') {
            window.messengerTheme = 'light';
            console.log('Overridden messengerTheme to light');
        }
        
        // Debug: Inspect emoji picker structure
        setTimeout(() => {
            const emojiPickerElement = document.querySelector('.emoji-picker');
            if (emojiPickerElement) {
                console.log('=== EMOJI PICKER STRUCTURE DEBUG ===');
                console.log('Main emoji picker element:', emojiPickerElement);
                console.log('Classes:', emojiPickerElement.className);
                console.log('Children:', emojiPickerElement.children);
                
                // Log all child elements and their classes
                Array.from(emojiPickerElement.querySelectorAll('*')).forEach((el, index) => {
                    console.log(`Child ${index}:`, el.tagName, el.className, el.textContent?.substring(0, 50));
                });
                console.log('=== END DEBUG ===');
                
                // Force apply styles dynamically
                console.log('Force applying emoji picker styles...');
                
                // Force box-sizing
                const contentElements = emojiPickerElement.querySelectorAll('[class*="content"], [class*="emoji-area"]');
                contentElements.forEach(el => {
                    el.style.setProperty('box-sizing', 'initial', 'important');
                    el.style.setProperty('-webkit-box-sizing', 'initial', 'important');
                    el.style.setProperty('-moz-box-sizing', 'initial', 'important');
                    console.log('Applied box-sizing to:', el.className);
                });
                
                // Force border colors
                const contentEls = emojiPickerElement.querySelectorAll('[class*="content"]');
                contentEls.forEach(el => {
                    el.style.setProperty('border-bottom', '1px solid #f1f5f9', 'important');
                    el.style.setProperty('border-top', '1px solid #f1f5f9', 'important');
                    console.log('Applied border to:', el.className);
                });
                
                // Force tab icon colors
                const tabButtons = emojiPickerElement.querySelectorAll('[class*="category-button"]');
                tabButtons.forEach(btn => {
                    const svgs = btn.querySelectorAll('svg');
                    const icons = btn.querySelectorAll('i');
                    
                    svgs.forEach(svg => {
                        svg.style.setProperty('color', '#6b7280', 'important');
                        svg.style.setProperty('fill', '#6b7280', 'important');
                    });
                    
                    icons.forEach(icon => {
                        icon.style.setProperty('color', '#6b7280', 'important');
                    });
                    
                    // Check if active
                    if (btn.classList.contains('active')) {
                        svgs.forEach(svg => {
                            svg.style.setProperty('color', '#3b82f6', 'important');
                            svg.style.setProperty('fill', '#3b82f6', 'important');
                        });
                        icons.forEach(icon => {
                            icon.style.setProperty('color', '#3b82f6', 'important');
                        });
                    }
                    
                    console.log('Applied tab styling to:', btn.className);
                });
                
                console.log('Dynamic styling applied!');
            }
        }, 3000);
    }, 2000);
    
    // Real-time favorite updates via Reverb/Pusher
    if (typeof pusher !== 'undefined') {
        // Listen for favorite updates on the user's private channel
        pusher.subscribe('private-chatify.{{ Auth::id() }}')
            .bind('favorite-updated', function(data) {
                console.log('Favorite updated via Reverb:', data);
                // Reload favorites list
                if (typeof getFavoritesList === 'function') {
                    getFavoritesList();
                }
                
                // Update star button if on the same contact
                const currentContactId = $('.header-contact-widget').attr('data-contact-id');
                if (currentContactId == data.user_id) {
                    if (data.status > 0) {
                        $(".add-to-favorite").addClass("favorite");
                        $(".add-to-favorite").removeClass('text-slate-600 hover:text-yellow-500').addClass('text-yellow-500');
                        $(".add-to-favorite i").removeClass('text-slate-600 text-yellow-500 ri-star-line').addClass('text-yellow-500 ri-star-fill');
                        $(".add-to-favorite").attr('title', 'Remove from favorites');
                    } else {
                        $(".add-to-favorite").removeClass("favorite");
                        $(".add-to-favorite").removeClass('text-yellow-500').addClass('text-slate-600 hover:text-yellow-500');
                        $(".add-to-favorite i").removeClass('text-slate-600 text-yellow-500 ri-star-fill').addClass('text-slate-600 ri-star-line');
                        $(".add-to-favorite").attr('title', 'Add to favorites');
                    }
                }
            });
    }

            // Watch for changes to the star button and fix them immediately
            const starButtonObserver = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        const target = mutation.target;
                        if (target.classList.contains('add-to-favorite')) {
                            console.log('Star button classes changed, checking favorite status...');
                            setTimeout(() => {
                                if (currentContactId) {
                                    checkAndUpdateFavoriteButton(currentContactId);
                                }
                            }, 100);
                        }
                    }
                });
            });

            // Start observing the star button
            const starButton = document.querySelector('.add-to-favorite');
            if (starButton) {
                starButtonObserver.observe(starButton, {
                    attributes: true,
                    attributeFilter: ['class']
                });
                console.log('Star button observer started');
            }

        // Scroll to Bottom Button Functionality
        initScrollToBottomButton();
        
        // User Status Management
        initUserStatusManagement();
    
    // ==========================
    // UNIFIED IDinfo OVERRIDE - Combines all functionality
    // ==========================
    setTimeout(() => {
        storedOriginalIDinfo = window.IDinfo;
        
        if (typeof storedOriginalIDinfo === 'function') {
            window.IDinfo = function(id) {
                console.log('=== UNIFIED IDinfo CALLED ===', id);
                
                // Validate ID
                if (!id || id === 'undefined' || id === 'null') {
                    console.warn('Invalid ID passed to IDinfo:', id);
                    return;
                }
                
                // 1. Hide empty state
                toggleEmptyState(false);
                
                // 2. Store the ID
                currentContactId = id;
                
                // 3. Call original IDinfo
                const result = storedOriginalIDinfo(id);
                
                // 4. Wait for Chatify's AJAX to complete, then update our custom elements
                setTimeout(() => {
                    const contactName = $('.m-header-messaging .user-name').text();
                    const contactAvatar = $('.header-avatar').css('background-image');
                    
                    if (contactName) {
                        let avatarUrl = null;
                        if (contactAvatar && contactAvatar !== 'none') {
                            const matches = contactAvatar.match(/url\(["']?([^"']*)["']?\)/);
                            if (matches && matches[1]) {
                                avatarUrl = matches[1];
                            }
                        }
                        updateHeaderContact(id, contactName, avatarUrl);
                    }
                    
                    // 5. Update star button
                    setTimeout(() => {
                        checkAndUpdateFavoriteButton(id);
                    }, 1000);
                    
                    // 6. Update status
                    $.ajax({
                        url: '{{ url(config("chatify.routes.prefix")) }}/idInfo',
                        method: "POST",
                        data: { _token: '{{ csrf_token() }}', id: id },
                        dataType: "JSON",
                        success: function(data) {
                            if (data && data.fetch && typeof window.updateChatHeaderStatus === 'function') {
                                window.updateChatHeaderStatus(data);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('IDinfo status error:', error);
                        }
                    });
                }, 800);
                
                return result;
            };
            
            console.log('Unified IDinfo override installed');
        }
    }, 100);
        
        // Override makeSeen function to properly remove unread counter
        window.makeSeen = function(status) {
            if (document?.hidden) {
                return;
            }
            
            const messengerId = getMessengerId();
            if (!messengerId) return;
            
            // Remove unseen counter for the user from the contacts list
            $(".messenger-list-item[data-contact=" + messengerId + "]")
                .find("span.inline-flex.items-center.justify-center.w-5.h-5.bg-indigo-600")
                .remove();
            
            // Also try alternative selectors in case the classes change
            $(".messenger-list-item[data-contact=" + messengerId + "]")
                .find("span")
                .filter(function() {
                    return $(this).text().match(/^\d+$/) && $(this).hasClass('bg-indigo-600');
                })
                .remove();
            
            // Make AJAX call to mark messages as seen
            $.ajax({
                url: '{{ route("messages.seen") }}',
                method: "POST",
                data: { _token: '{{ csrf_token() }}', id: messengerId },
                dataType: "JSON",
                success: function(data) {
                    console.log('Messages marked as seen:', data);
                },
                error: function(xhr, status, error) {
                    console.error('Error marking messages as seen:', error);
                }
            });
            
            // Trigger Pusher event if available
            if (typeof clientSendChannel !== 'undefined' && clientSendChannel) {
                return clientSendChannel.trigger("client-seen", {
                    from_id: auth_id,
                    to_id: messengerId,
                    seen: status,
                });
            }
        };
        
        console.log('Chatify custom functionality initialized');
        
        // Scroll to Bottom Button Functionality
        function initScrollToBottomButton() {
            const messagesContainer = document.querySelector('.messages-container');
            const scrollToBottomBtn = document.getElementById('scroll-to-bottom-btn');
            
            if (!messagesContainer || !scrollToBottomBtn) {
                console.log('Scroll to bottom elements not found');
                return;
            }
            
            let isScrolling = false;
            let scrollTimeout;
            
            // Function to show/hide the scroll to bottom button
            function toggleScrollToBottomButton() {
                const scrollTop = messagesContainer.scrollTop;
                const scrollHeight = messagesContainer.scrollHeight;
                const clientHeight = messagesContainer.clientHeight;
                const isAtBottom = scrollTop + clientHeight >= scrollHeight - 10; // 10px threshold
                
                if (isAtBottom) {
                    // Hide button with animation
                    scrollToBottomBtn.style.opacity = '0';
                    scrollToBottomBtn.style.transform = 'translateY(16px)';
                } else {
                    // Show button with animation
                    scrollToBottomBtn.style.opacity = '1';
                    scrollToBottomBtn.style.transform = 'translateY(0)';
                }
            }
            
            // Function to scroll to bottom smoothly
            function scrollToBottom() {
                messagesContainer.scrollTo({
                    top: messagesContainer.scrollHeight,
                    behavior: 'smooth'
                });
            }
            
            // Listen for scroll events
            messagesContainer.addEventListener('scroll', function() {
                if (!isScrolling) {
                    isScrolling = true;
                    requestAnimationFrame(function() {
                        toggleScrollToBottomButton();
                        isScrolling = false;
                    });
                }
            });
            
            // Listen for click on scroll to bottom button
            scrollToBottomBtn.addEventListener('click', function(e) {
                e.preventDefault();
                scrollToBottom();
            });
            
            // Listen for new messages to auto-scroll if user is near bottom
            const messagesObserver = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                        // Check if user is near bottom (within 100px)
                        const scrollTop = messagesContainer.scrollTop;
                        const scrollHeight = messagesContainer.scrollHeight;
                        const clientHeight = messagesContainer.clientHeight;
                        const isNearBottom = scrollTop + clientHeight >= scrollHeight - 100;
                        
                        if (isNearBottom) {
                            // Auto-scroll to bottom for new messages
                            setTimeout(() => {
                                scrollToBottom();
                            }, 100);
                        }
                        
                        // Update button visibility
                        setTimeout(() => {
                            toggleScrollToBottomButton();
                        }, 200);
                    }
                });
            });
            
            // Start observing messages container for new messages
            messagesObserver.observe(messagesContainer.querySelector('.messages'), {
                childList: true,
                subtree: true
            });
            
            // Initial check
            setTimeout(() => {
                toggleScrollToBottomButton();
            }, 500);
            
            console.log('Scroll to bottom button initialized');
        }
        
        // User Status Management Functionality - Define globally accessible function
        window.updateChatHeaderStatus = function(data) {
            console.log('updateChatHeaderStatus called with:', data);
            
            if (data && data.fetch) {
                const userName = data.fetch.name || 'User';
                const userAvatar = data.user_avatar || null;
                const isOnline = data.fetch.active_status === 1 || data.fetch.active_status === true;
                const avatarColor = data.fetch.avatar_color || 'bg-slate-100 text-slate-600';
                
                console.log('updateChatHeaderStatus - active_status:', data.fetch.active_status, 'isOnline:', isOnline);
                console.log('updateChatHeaderStatus - avatar_color:', avatarColor);
                updateHeaderAvatar(userName, userAvatar, isOnline, avatarColor);
                console.log('✅ updateChatHeaderStatus updated avatar with status:', isOnline);
            }
        };
        
        function initUserStatusManagement() {
            console.log('Initializing user status management...');
            
            // Listen for status updates via Pusher (if available) - disabled for sidebar
            if (typeof clientListenChannel !== 'undefined' && clientListenChannel) {
                clientListenChannel.bind("client-activeStatus", function(data) {
                    console.log('Pusher status update received but not processing for sidebar:', data);
                    // Sidebar uses avatar component with server-side status
                    // No need to update sidebar status via Pusher
                });
            }
            
            // Set current user as online when page loads
            if (typeof setActiveStatus === 'function') {
                setActiveStatus(1);
            }
            
            // Set user as offline when page unloads
            window.addEventListener('beforeunload', function() {
                if (typeof setActiveStatus === 'function') {
                    setActiveStatus(0);
                }
            });
            
            console.log('User status management initialized');
        }

}); // End of DOMContentLoaded
</script>
