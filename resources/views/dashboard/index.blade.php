@extends('layouts.app')

@section('title', 'Dashboard - People Of Data')

@section('content')
<!-- Alert Container -->
<div id="alert-container"></div>

<!-- PhotoSwipe will be initialized dynamically -->

<div class="w-full" style="min-width: 0;">
    <div class="flex flex-col lg:flex-row lg:gap-8 gap-6 w-full" style="width: 100%; min-width: 0;">
        <!-- Main Content Area -->
        <div class="flex-1 w-full lg:max-w-3xl min-w-0 lg:flex-shrink-0">
            <!-- Post Creation -->
            <div class="bg-white rounded-xl p-4 lg:p-6 mb-6 w-full border border-indigo-100">
            <form id="post-form" enctype="multipart/form-data">
                @csrf
                <div class="flex items-start space-x-3 lg:space-x-4">
                    <x-avatar 
                        :src="auth()->user()->avatar ?? null"
                        :name="auth()->user()->name ?? 'User'"
                        size="md"
                        :color="auth()->user()->avatar_color ?? null" />
                    <div class="flex-1 min-w-0">
                        <textarea name="content" id="post-content" placeholder="Share your thoughts with the community..."
                            class="w-full p-3 lg:p-4 border border-slate-200 rounded-lg resize-none focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm lg:text-base"
                            rows="3"></textarea>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mt-4 space-y-3 sm:space-y-0 pl-14">
                    <div class="flex space-x-4">
                        <label class="flex items-center space-x-2 text-slate-600 hover:text-indigo-600 transition-colors cursor-pointer">
                            <input type="file" name="images[]" id="post-images" multiple accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" class="hidden">
                            <div class="w-5 h-5 flex items-center justify-center">
                                <i class="ri-image-line"></i>
                            </div>
                            <span class="text-base">Photo</span>
                        </label>
                    </div>
                    <button type="submit" id="submit-post"
                        class="bg-indigo-600 text-white px-4 lg:px-6 py-2 rounded-button hover:bg-indigo-700 transition-colors !rounded-button whitespace-nowrap text-sm lg:text-base w-full sm:w-auto">
                        <span id="submit-text">Share Post</span>
                        <div id="submit-loading" class="hidden">
                            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                        </div>
                    </button>
                </div>
                <!-- Image Preview -->
                <div id="image-preview" class="mt-4 hidden">
                    <div class="flex flex-wrap gap-2" id="preview-container"></div>
                </div>
            </form>
        </div>

        <!-- Feed Posts -->
        <div id="posts-container" class="space-y-6">
            @foreach($posts as $index => $post)
                @include('components.post-card', ['post' => $post])
                
                @if($index == 1)
                    <!-- Events Section - Show after 2 posts -->
                    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 lg:p-6">
                        <div class="flex items-center justify-between mb-4 lg:mb-6">
                            <h3 class="text-lg font-semibold text-slate-800">Upcoming Events</h3>
                            <a href="{{ route('events.index') }}" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">View All</a>
                        </div>
                        <div class="flex space-x-3 lg:space-x-4 overflow-x-auto pb-4 -mx-4 px-4" id="events-carousel">
                            @foreach($events as $event)
                                <div class="flex-none w-80 bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl p-4 lg:p-6 border border-slate-100">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="bg-white rounded-lg px-3 py-2 shadow-sm">
                                            <div class="text-xs text-indigo-600 font-medium">{{ $event->start_date->format('M') }}</div>
                                            <div class="text-lg font-bold text-slate-800">{{ $event->start_date->format('d') }}</div>
                                            <div class="text-xs text-slate-500">{{ $event->start_date->format('Y') }}</div>
                                        </div>
                                        <div class="bg-indigo-100 rounded-full p-2">
                                            <div class="w-6 h-6 flex items-center justify-center">
                                                <i class="ri-presentation-line text-indigo-600"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <h4 class="font-semibold text-slate-800 mb-2">{{ $event->title }}</h4>
                                    <p class="text-sm text-slate-600 mb-4">
                                        {{ Str::limit($event->description, 100) }}
                                    </p>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-1 text-indigo-600">
                                            <div class="w-4 h-4 flex items-center justify-center">
                                                <i class="ri-user-line text-xs"></i>
                                            </div>
                                            <span class="text-sm font-medium">{{ $event->max_attendees ?? 'Unlimited' }} spots</span>
                                        </div>
                                        <a href="{{ route('events.show', $event) }}"
                                            class="bg-indigo-600 text-white px-4 py-2 rounded-button text-sm hover:bg-indigo-700 transition-colors !rounded-button whitespace-nowrap">
                                            Join Event
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <!-- Loading Indicator -->
        <div id="loading-indicator" class="hidden text-center py-8">
            <div class="inline-flex items-center space-x-2 text-slate-500">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600"></div>
                <span>Loading more posts...</span>
            </div>
        </div>

        <!-- No More Posts -->
        <div id="no-more-posts" class="hidden text-center py-8">
            <p class="text-slate-500">You've reached the end of the feed!</p>
        </div>
    </div>

        <!-- Right Sidebar -->
        <div class="w-full lg:w-80 lg:flex-shrink-0 min-w-0">
            <div class="space-y-4 lg:space-y-6 w-full">
            <!-- Community Pulse -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 lg:p-6">
                <h3 class="font-semibold text-slate-800 mb-3 lg:mb-4">Community Pulse</h3>
                <div class="space-y-3 lg:space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <i class="ri-user-add-line text-slate-400"></i>
                            <span class="text-sm text-slate-600">New Members</span>
                        </div>
                        <span class="text-sm font-semibold text-indigo-600">{{ \App\Models\User::whereDate('created_at', today())->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <i class="ri-file-text-line text-slate-400"></i>
                            <span class="text-sm text-slate-600">Posts Today</span>
                        </div>
                        <span class="text-sm font-semibold text-green-600">{{ \App\Models\Post::published()->whereDate('created_at', today())->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <i class="ri-chat-3-line text-slate-400"></i>
                            <span class="text-sm text-slate-600">Comments Today</span>
                        </div>
                        <span class="text-sm font-semibold text-purple-600">{{ \App\Models\Comment::whereDate('created_at', today())->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <i class="ri-calendar-event-line text-slate-400"></i>
                            <span class="text-sm text-slate-600">Events This Week</span>
                        </div>
                        <span class="text-sm font-semibold text-rose-600">{{ \App\Models\Event::whereBetween('start_date', [now(), now()->addWeek()])->count() }}</span>
                    </div>
                </div>
            </div>

            <!-- Trending Posts -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 lg:p-6">
                <h3 class="font-semibold text-slate-800 mb-3 lg:mb-4">Trending Posts</h3>
                <div class="space-y-3 lg:space-y-4">
                    @foreach(\App\Models\Post::published()->withCount('comments')->orderBy('likes_count', 'desc')->take(3)->get() as $trendingPost)
                        <a href="{{ route('posts.show', $trendingPost) }}" class="block hover:bg-slate-50 rounded-lg p-3 -m-3 transition-colors group">
                            <div class="flex space-x-3">
                                <div class="flex-shrink-0">
                                    <x-avatar 
                                        :src="$trendingPost->user->avatar ?? null"
                                        :name="$trendingPost->user->name ?? 'User'"
                                        size="sm"
                                        :color="$trendingPost->user->avatar_color ?? null" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="mb-1.5">
                                        <div class="text-sm font-semibold text-slate-800">{{ $trendingPost->user->name ?? 'User' }}</div>
                                        <div class="text-xs text-slate-500 mt-0.5">{{ $trendingPost->created_at->diffForHumans() }}</div>
                                    </div>
                                    <p class="text-sm text-slate-700 line-clamp-2">
                                        {{ Str::limit($trendingPost->content, 80) }}
                                    </p>
                                    <div class="flex items-center space-x-3 mt-2">
                                        <div class="flex items-center space-x-1 text-xs text-slate-500">
                                            <i class="ri-heart-line"></i>
                                            <span>{{ $trendingPost->likes_count }}</span>
                                        </div>
                                        <div class="flex items-center space-x-1 text-xs text-slate-500">
                                            <i class="ri-chat-3-line"></i>
                                            <span>{{ $trendingPost->comments_count }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Active Members -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 lg:p-6">
                <h3 class="font-semibold text-slate-800 mb-3 lg:mb-4">Recent Members</h3>
                <div class="space-y-2 lg:space-y-3">
                    @foreach(\App\Models\User::latest()->take(5)->get() as $user)
                        <a href="{{ route('profile.show.other', $user->id) }}" class="flex items-center space-x-3 hover:bg-slate-50 rounded-lg p-2 -m-2 transition-colors group">
                            <x-avatar 
                                :src="$user->avatar ?? null"
                                :name="$user->name ?? 'User'"
                                size="sm"
                                :color="$user->avatar_color ?? null" />
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-800 group-hover:text-indigo-600 transition-colors truncate">{{ $user->name }}</p>
                                <p class="text-xs text-slate-500 truncate">Joined {{ $user->created_at->diffForHumans() }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Comment Modal -->
<x-comment-modal />

<!-- Include Confirmation Modal -->
<x-confirmation-modal id="delete-post-modal" />

@endsection

@push('scripts')
<style>
    .like-btn:hover {
        color: #ef4444 !important;
    }
    .like-btn.text-red-500:hover {
        color: #dc2626 !important;
    }
    
    /* Remove elastic scroll effect */
    html, body {
        overscroll-behavior: none;
        -webkit-overflow-scrolling: touch;
    }
    
    * {
        overscroll-behavior: none;
    }
</style>
<script>
    // Set current user ID for JavaScript
    window.currentUserId = {{ auth()->id() }};
    
    // Toggle post options dropdown
    window.togglePostOptions = function(postId) {
        const dropdown = document.getElementById(`post-options-${postId}`);
        if (dropdown) {
            // Close all other dropdowns
            document.querySelectorAll('[id^="post-options-"]').forEach(menu => {
                if (menu.id !== `post-options-${postId}`) {
                    menu.classList.add('hidden');
                }
            });
            
            // Toggle current dropdown
            dropdown.classList.toggle('hidden');
        }
    };
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('[id^="post-options-"]') && !event.target.closest('button[onclick*="togglePostOptions"]')) {
            document.querySelectorAll('[id^="post-options-"]').forEach(menu => {
                menu.classList.add('hidden');
            });
        }
    });
    
    document.addEventListener("DOMContentLoaded", function () {
        let currentOffset = {{ $posts->count() }};
        let isLoading = false;
        let hasMorePosts = true;

        // Post form submission
        const postForm = document.getElementById('post-form');
        const submitBtn = document.getElementById('submit-post');
        const submitText = document.getElementById('submit-text');
        const submitLoading = document.getElementById('submit-loading');

        postForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (isLoading) return;
            
            // Validate file sizes before submitting
            const validationImageInput = document.getElementById('post-images');
            if (validationImageInput.files.length > 0) {
                const maxSize = 2 * 1024 * 1024; // 2MB in bytes
                const maxFiles = 10; // Maximum 10 images
                const oversizedFiles = [];
                
                // Check file count
                if (validationImageInput.files.length > maxFiles) {
                    showError(`You can upload a maximum of ${maxFiles} images per post. Please remove ${validationImageInput.files.length - maxFiles} image(s).`, 'Too Many Images');
                    return;
                }
                
                // Check file sizes
                for (let i = 0; i < validationImageInput.files.length; i++) {
                    if (validationImageInput.files[i].size > maxSize) {
                        oversizedFiles.push(validationImageInput.files[i].name);
                    }
                }
                
                if (oversizedFiles.length > 0) {
                    showError(`The following images are too large (max 2MB each): ${oversizedFiles.join(', ')}. Please compress or choose smaller images.`, 'File Size Error');
                    return;
                }
            }
            
            // Server will validate that post has either text or images
            
            isLoading = true;
            submitBtn.disabled = true;
            submitText.classList.add('hidden');
            submitLoading.classList.remove('hidden');

            // Create FormData manually to have full control
            const formData = new FormData();
            
            // Add CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            formData.append('_token', csrfToken);
            
            // Add content field (empty string if no text)
            const postContent = document.getElementById('post-content').value.trim();
            formData.append('content', postContent || '');
            
            // Add images if any
            const postImageInput = document.getElementById('post-images');
            for (let i = 0; i < postImageInput.files.length; i++) {
                formData.append('images[]', postImageInput.files[i]);
            }
            
            // Add required fields
            formData.append('type', 'text');
            formData.append('is_published', '1');
            
            // Debug: Log form data to see what's being sent
            console.log('Form data being sent:');
            for (let [key, value] of formData.entries()) {
                console.log(key, value);
            }

            try {
                const response = await fetch('{{ route("posts.store") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    console.error('Validation errors:', errorData);
                    throw new Error(`HTTP error! status: ${response.status} - ${errorData.message || 'Validation failed'}`);
                }

                const data = await response.json();
                
                if (data.success) {
                    // Add new post to the top of the feed
                    const postsContainer = document.getElementById('posts-container');
                    const newPostHtml = createPostHtml(data.post);
                    postsContainer.insertAdjacentHTML('afterbegin', newPostHtml);
                    
                    // Clear form
                    postForm.reset();
                    document.getElementById('image-preview').classList.add('hidden');
                    document.getElementById('preview-container').innerHTML = '';
                    
                    // Show success message
                    showSuccess('Your post has been shared successfully!', 'Post Published');
                } else {
                    console.error('Server error:', data.message);
                    showError('Unable to create post. Please try again.', 'Post Error');
                }
            } catch (error) {
                console.error('Network error:', error);
                showError('Unable to create post. Please check your connection and try again.', 'Network Error');
            } finally {
                isLoading = false;
                submitBtn.disabled = false;
                submitText.classList.remove('hidden');
                submitLoading.classList.add('hidden');
            }
        });

        // Image preview functionality
        const imageInput = document.getElementById('post-images');
        const imagePreview = document.getElementById('image-preview');
        const previewContainer = document.getElementById('preview-container');

        imageInput.addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            const maxFiles = 10; // Maximum 10 images
            const maxSize = 2 * 1024 * 1024; // 2MB in bytes
            const oversizedFiles = [];
            
            // Check file count
            if (files.length > maxFiles) {
                showError(`You can upload a maximum of ${maxFiles} images per post. Please select fewer images.`, 'Too Many Images');
                e.target.value = ''; // Clear the input
                return;
            }

            // Validate file sizes immediately
            files.forEach(file => {
                if (file.size > maxSize) {
                    oversizedFiles.push(file.name);
                }
            });
            
            if (oversizedFiles.length > 0) {
                showError(`The following images are too large (max 2MB each): ${oversizedFiles.join(', ')}. Please choose smaller images.`, 'File Size Error');
                e.target.value = ''; // Clear the input
                return;
            }

            previewContainer.innerHTML = '';
            
            files.forEach((file, index) => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const previewItem = document.createElement('div');
                        previewItem.className = 'relative inline-block';
                        previewItem.dataset.index = index;
                        
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'w-20 h-20 object-cover rounded-lg';
                        
                        const removeBtn = document.createElement('button');
                        removeBtn.type = 'button';
                        removeBtn.className = 'absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors';
                        removeBtn.innerHTML = '<i class="ri-close-line text-xs"></i>';
                        removeBtn.onclick = function() {
                            removeImage(index);
                        };
                        
                        previewItem.appendChild(img);
                        previewItem.appendChild(removeBtn);
                        previewContainer.appendChild(previewItem);
                    };
                    reader.readAsDataURL(file);
                }
            });

            if (files.length > 0) {
                imagePreview.classList.remove('hidden');
            } else {
                imagePreview.classList.add('hidden');
            }
        });

        // Function to remove an image from preview
        function removeImage(index) {
            const files = Array.from(imageInput.files);
            files.splice(index, 1);
            
            // Create new FileList
            const dt = new DataTransfer();
            files.forEach(file => dt.items.add(file));
            imageInput.files = dt.files;
            
            // Re-render preview
            imageInput.dispatchEvent(new Event('change'));
        }

        // Infinite scroll
        const loadingIndicator = document.getElementById('loading-indicator');
        const noMorePosts = document.getElementById('no-more-posts');

        function loadMorePosts() {
            if (isLoading || !hasMorePosts) return;

            isLoading = true;
            loadingIndicator.classList.remove('hidden');

            fetch(`{{ route('posts.load-more') }}?offset=${currentOffset}`)
                .then(response => response.json())
                .then(data => {
                    if (data.posts && data.posts.length > 0) {
                        const postsContainer = document.getElementById('posts-container');
                        data.posts.forEach(post => {
                            const postHtml = createPostHtml(post);
                            postsContainer.insertAdjacentHTML('beforeend', postHtml);
                        });
                        currentOffset = data.nextOffset;
                        hasMorePosts = data.hasMore;
                    } else {
                        hasMorePosts = false;
                        noMorePosts.classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error loading posts:', error);
                })
                .finally(() => {
                    isLoading = false;
                    loadingIndicator.classList.add('hidden');
                });
        }

        // Scroll event listener for infinite scroll
        window.addEventListener('scroll', function() {
            if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 1000) {
                loadMorePosts();
            }
        });

        // Events carousel interaction
        const carousel = document.getElementById("events-carousel");
        if (carousel) {
            let isDown = false;
            let startX;
            let scrollLeft;
            
            carousel.addEventListener("mousedown", (e) => {
                isDown = true;
                startX = e.pageX - carousel.offsetLeft;
                scrollLeft = carousel.scrollLeft;
                carousel.style.cursor = "grabbing";
            });
            
            carousel.addEventListener("mouseleave", () => {
                isDown = false;
                carousel.style.cursor = "grab";
            });
            
            carousel.addEventListener("mouseup", () => {
                isDown = false;
                carousel.style.cursor = "grab";
            });
            
            carousel.addEventListener("mousemove", (e) => {
                if (!isDown) return;
                e.preventDefault();
                const x = e.pageX - carousel.offsetLeft;
                const walk = (x - startX) * 2;
                carousel.scrollLeft = scrollLeft - walk;
            });
        }

        // Post interactions (like buttons and comment buttons)
        document.addEventListener('click', function(e) {
            if (e.target.closest('.like-btn')) {
                const button = e.target.closest('.like-btn');
                const postId = button.dataset.postId;
                const icon = button.querySelector('i');
                const countSpan = button.querySelector('.likes-count');
                const isLiked = button.dataset.liked === 'true';
                
                fetch(`/posts/${postId}/like`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.liked) {
                            icon.classList.remove("ri-heart-line");
                            icon.classList.add("ri-heart-fill");
                            button.classList.remove("text-slate-500");
                            button.classList.add("text-red-500");
                            button.dataset.liked = 'true';
                        } else {
                            icon.classList.remove("ri-heart-fill");
                            icon.classList.add("ri-heart-line");
                            button.classList.remove("text-red-500");
                            button.classList.add("text-slate-500");
                            button.dataset.liked = 'false';
                        }
                        countSpan.textContent = data.likes_count;
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        });

        // Smooth card animations - removed hover translateY effects
        // const cards = document.querySelectorAll("article, .bg-white");
        // cards.forEach((card) => {
        //     card.addEventListener("mouseenter", function () {
        //         this.style.transform = "translateY(-2px)";
        //         this.style.transition = "transform 0.2s ease";
        //     });
        //     card.addEventListener("mouseleave", function () {
        //         this.style.transform = "translateY(0)";
        //     });
        // });
    });

    function createPostHtml(post) {
        // Create proper avatar HTML with image support (size="sm" = w-8 h-8)
        const avatarHtml = post.user.avatar 
            ? `<img src="${post.user.avatar}" alt="${post.user.name || 'User'}" class="w-8 h-8 rounded-full object-cover">`
            : `<div class="w-8 h-8 ${post.user.avatar_color || 'bg-slate-100 text-slate-600'} rounded-full flex items-center justify-center">
                <span class="font-semibold text-sm">${post.user.name ? post.user.name.substring(0, 2).toUpperCase() : 'U'}</span>
               </div>`;
        
        // Use "just now" for new posts, otherwise use relative time
        const timeAgo = post.created_at ? 'just now' : 'just now';
        
        const optionsMenu = post.user_id === window.currentUserId ? `
                <div class="relative">
                    <button onclick="togglePostOptions(${post.id})" class="text-slate-400 hover:text-slate-600 transition-colors p-1">
                        <i class="ri-more-2-fill text-lg"></i>
                    </button>
                    
                    <div id="post-options-${post.id}" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-slate-200 z-10">
                        <div class="py-1">
                            <a href="/posts/${post.id}/edit" class="flex items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                <i class="ri-pencil-line mr-2"></i>
                                Edit
                            </a>
                            <button onclick="deletePost(${post.id})" class="w-full text-left flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                <i class="ri-delete-bin-line mr-2"></i>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
        ` : '';
        
        return `
            <article class="bg-white rounded-xl shadow-[0_8px_24px_rgba(0,0,0,0.04)] p-6 relative">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3 flex-1">
                        ${avatarHtml}
                        <div>
                            <h4 class="font-semibold text-slate-800">${post.user.name || 'Anonymous'}</h4>
                            <p class="text-sm text-slate-500">${post.user.job_title || 'Member'} • 
                                <a href="/posts/${post.id}" class="hover:text-indigo-600 transition-colors">${timeAgo}</a>
                            </p>
                        </div>
                    </div>
                    ${optionsMenu}
                </div>
                ${post.content ? `<p class="text-slate-700 mb-4">${post.content}</p>` : ''}
                ${post.images && post.images.length > 0 ? `
                    <div class="mb-4" data-post-images='${JSON.stringify(post.images)}'>
                        <div class="grid grid-cols-2 gap-2">
                            ${post.images.slice(0, 4).map((image, index) => `
                                <div class="relative w-full aspect-square cursor-pointer group" onclick="openLightbox(${post.id}, ${index})" data-post-id="${post.id}" data-image-index="${index}">
                                    <img src="/storage/${image}" 
                                         alt="Post image" 
                                         class="w-full h-full object-cover group-hover:opacity-90 transition-opacity">
                                    ${index === 3 && post.images.length > 4 ? `
                                        <div class="absolute inset-0 bg-black bg-opacity-60 flex items-center justify-center">
                                            <div class="text-center text-white">
                                                <div class="text-2xl font-bold">+${post.images.length - 4}</div>
                                                <div class="text-sm">more photos</div>
                                            </div>
                                        </div>
                                    ` : ''}
                                </div>
                            `).join('')}
                        </div>
                    </div>
                ` : ''}
                ${post.hashtags && post.hashtags.length > 0 ? `
                    <div class="flex flex-wrap gap-2 mb-4">
                        ${post.hashtags.map(hashtag => `
                            <span class="bg-indigo-100 text-indigo-600 px-2 py-1 rounded-full text-xs">${hashtag}</span>
                        `).join('')}
                    </div>
                ` : ''}
                <div class="-mx-6 -mb-6 border-t border-slate-100">
                    <div class="grid grid-cols-3 divide-x divide-slate-100">
                        <button class="like-btn flex items-center justify-center space-x-2 py-4 px-6 transition-colors ${post.is_liked ? 'text-red-500' : 'text-slate-500'}" data-post-id="${post.id}" data-liked="${post.is_liked || false}">
                            <div class="w-5 h-5 flex items-center justify-center">
                                <i class="${post.is_liked ? 'ri-heart-fill' : 'ri-heart-line'}"></i>
                            </div>
                            <span class="text-sm">Like</span>
                            <span class="text-sm likes-count">${post.likes_count || 0}</span>
                        </button>
                        <button class="comment-btn flex items-center justify-center space-x-2 hover:text-indigo-600 transition-colors text-slate-500 py-4 px-6" data-post-id="${post.id}">
                            <div class="w-5 h-5 flex items-center justify-center">
                                <i class="ri-chat-3-line"></i>
                            </div>
                            <span class="text-sm">Comment</span>
                            <span class="text-sm comments-count">${post.comments_count || 0}</span>
                        </button>
                        <button class="flex items-center justify-center space-x-2 hover:text-indigo-600 transition-colors text-slate-500 py-4 px-6">
                            <div class="w-5 h-5 flex items-center justify-center">
                                <i class="ri-share-line"></i>
                            </div>
                            <span class="text-sm">Share</span>
                        </button>
                    </div>
                </div>
            </article>
        `;
    }

    // Comment Modal Management
    class CommentManager {
        constructor() {
            this.modal = document.getElementById('comment-modal');
            this.backdrop = document.getElementById('comment-modal-backdrop');
            this.currentPostId = null;
            this.isReplyMode = false;
            this.parentCommentId = null;
            
            this.initializeElements();
            this.bindEvents();
        }

        initializeElements() {
            this.modalPostContent = document.getElementById('modal-post-content');
            this.commentsList = document.getElementById('comments-list');
            this.loadingComments = document.getElementById('loading-comments');
            this.noComments = document.getElementById('no-comments');
            this.commentForm = document.getElementById('comment-form');
            this.postIdInput = document.getElementById('post-id');
            this.parentCommentInput = document.getElementById('parent-comment-id');
            this.commentContent = document.getElementById('comment-content');
            this.submitBtn = document.getElementById('submit-comment');
            this.submitText = document.getElementById('submit-text');
            this.submitLoading = document.getElementById('submit-loading');
            this.replyingTo = document.getElementById('replying-to');
            this.replyToUser = document.getElementById('reply-to-user');
            this.cancelReply = document.getElementById('cancel-reply');
        }

        bindEvents() {
            // Modal open/close events
            document.addEventListener('click', (e) => {
                if (e.target.closest('.comment-btn')) {
                    const button = e.target.closest('.comment-btn');
                    const postId = button.dataset.postId;
                    this.openModal(postId);
                }
            });

            // Close modal on backdrop click
            if (this.backdrop) {
                this.backdrop.addEventListener('click', () => {
                    this.closeModal();
                });
            }

            // Comment form events
            if (this.commentContent) {
                this.commentContent.addEventListener('input', () => {
                    this.toggleSubmitButton();
                });
            }

            if (this.commentForm) {
                this.commentForm.addEventListener('submit', (e) => {
                    e.preventDefault();
                    this.submitComment();
                });
            }

            if (this.cancelReply) {
                this.cancelReply.addEventListener('click', () => {
                    this.cancelReplyMode();
                });
            }

            // Dynamic comment events (using event delegation)
            if (this.commentsList) {
                this.commentsList.addEventListener('click', (e) => {
                    if (e.target.closest('.reply-btn')) {
                        const button = e.target.closest('.reply-btn');
                        const commentId = button.dataset.commentId;
                        const userName = button.dataset.userName;
                        this.startReply(commentId, userName);
                    } else if (e.target.closest('.edit-comment-btn')) {
                        const button = e.target.closest('.edit-comment-btn');
                        const commentId = button.dataset.commentId;
                        this.startEdit(commentId);
                    } else if (e.target.closest('.delete-comment-btn')) {
                        const button = e.target.closest('.delete-comment-btn');
                        const commentId = button.dataset.commentId;
                        this.deleteComment(commentId);
                    } else if (e.target.closest('.cancel-edit-btn')) {
                        const button = e.target.closest('.cancel-edit-btn');
                        const commentId = button.closest('.edit-comment-form').dataset.commentId;
                        this.cancelEdit(commentId);
                    }
                });

                // Edit form submission
                this.commentsList.addEventListener('submit', (e) => {
                    if (e.target.classList.contains('edit-comment-form')) {
                        e.preventDefault();
                        const commentId = e.target.dataset.commentId;
                        const content = e.target.querySelector('textarea').value;
                        this.updateComment(commentId, content);
                    }
                });
            }
        }

        async openModal(postId) {
            this.currentPostId = postId;
            if (this.postIdInput) this.postIdInput.value = postId;
            
            // Show modal
            if (this.modal) {
                this.modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }
            
            // Load post content and comments
            await Promise.all([
                this.loadPostContent(postId),
                this.loadComments(postId)
            ]);
        }

        closeModal() {
            if (this.modal) {
                this.modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
            this.resetForm();
            this.currentPostId = null;
        }

        async loadPostContent(postId) {
            try {
                // Get post data from existing DOM
                const postElement = document.querySelector(`[data-post-id="${postId}"]`).closest('article');
                if (postElement && this.modalPostContent) {
                    // Clone the post content
                    const clonedPost = postElement.cloneNode(true);
                    
                    // Remove the action buttons row at the bottom (grid with Like/Comment/Share)
                    const actionButtonsDiv = clonedPost.querySelector('.grid.grid-cols-3');
                    if (actionButtonsDiv) actionButtonsDiv.remove();
                    
                    // Remove the options menu button
                    const optionsMenu = clonedPost.querySelector('[data-post-options]');
                    if (optionsMenu) {
                        optionsMenu.closest('.relative')?.remove();
                    }
                    
                    // Remove the three-dot menu button
                    const threeDotsMenu = clonedPost.querySelector('[id^="post-options-"]');
                    if (threeDotsMenu) {
                        threeDotsMenu.closest('.relative')?.remove();
                    }
                    
                    // Also remove any border/margin on the article element if present
                    clonedPost.classList.remove('shadow-[0_8px_24px_rgba(0,0,0,0.04)]');
                    clonedPost.classList.add('shadow-none');
                    
                    this.modalPostContent.innerHTML = '';
                    this.modalPostContent.appendChild(clonedPost);
                }
            } catch (error) {
                console.error('Error loading post content:', error);
            }
        }

        async loadComments(postId) {
            this.showLoadingState();
            
            try {
                const response = await fetch(`/posts/${postId}/comments`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                this.renderComments(data.comments.data || []);
            } catch (error) {
                console.error('Error loading comments:', error);
                this.showError('Failed to load comments');
            }
        }

        showLoadingState() {
            if (this.loadingComments && this.noComments && this.commentsList) {
                this.loadingComments.classList.remove('hidden');
                this.noComments.classList.add('hidden');
                this.commentsList.innerHTML = this.loadingComments.outerHTML;
            }
        }

        renderComments(comments) {
            if (!this.commentsList || !this.noComments) return;
            
            this.commentsList.innerHTML = '';
            
            if (comments.length === 0) {
                this.commentsList.appendChild(this.noComments.cloneNode(true));
                this.noComments.classList.remove('hidden');
            } else {
                const commentsContainer = document.createElement('div');
                commentsContainer.className = 'space-y-4';
                
                // Flatten nested comments to maximum 3 levels
                const flattenedComments = this.flattenComments(comments);
                flattenedComments.forEach(comment => {
                    commentsContainer.appendChild(this.createCommentElement(comment, comment.level));
                });
                
                this.commentsList.appendChild(commentsContainer);
            }
        }
        
        flattenComments(comments, level = 0, result = []) {
            comments.forEach(comment => {
                // Add the comment with its level
                const commentWithLevel = { ...comment, level };
                result.push(commentWithLevel);
                
                // If level is less than 2 (maximum 3 levels: 0, 1, 2), add replies
                // Level 2 replies won't be nested further, but they'll still be displayed
                if (comment.replies && comment.replies.length > 0) {
                    if (level < 2) {
                        // Can still nest further
                        this.flattenComments(comment.replies, level + 1, result);
                    } else {
                        // At max level, flatten replies at same level
                        comment.replies.forEach(reply => {
                            const replyWithLevel = { ...reply, level: level + 1 };
                            result.push(replyWithLevel);
                            
                            // Also handle any nested replies at this level
                            if (reply.replies && reply.replies.length > 0) {
                                this.flattenComments(reply.replies, level + 1, result);
                            }
                        });
                    }
                }
            });
            
            return result;
        }

        createCommentElement(comment, level = 0) {
            const div = document.createElement('div');
            div.className = 'comment-item';
            div.dataset.commentId = comment.id;
            div.style.marginLeft = `${level * 20}px`;
            
            div.innerHTML = `
                <div class="flex space-x-3">
                    <a href="/profile/${comment.user.id}" class="flex-shrink-0">
                        <div class="w-8 h-8 ${comment.user.avatar_color || 'bg-slate-100 text-slate-600'} rounded-full flex items-center justify-center cursor-pointer hover:opacity-80 transition-opacity">
                            <span class="font-semibold text-xs">
                                ${(comment.user.name || 'U').substring(0, 2).toUpperCase()}
                            </span>
                        </div>
                    </a>
                    
                    <div class="flex-1 min-w-0">
                        <div class="bg-slate-100 rounded-lg p-3">
                            <div class="flex items-center space-x-2 mb-1">
                                <a href="/profile/${comment.user.id}" class="hover:text-indigo-600 transition-colors">
                                    <h4 class="font-semibold text-sm text-slate-800">${comment.user.name || 'Anonymous'}</h4>
                                </a>
                                <span class="text-xs text-slate-500">${this.formatDate(comment.created_at)}</span>
                            </div>
                            <p class="text-sm text-slate-700 whitespace-pre-line">${comment.content}</p>
                        </div>
                        
                        <div class="flex items-center space-x-4 mt-2 text-xs text-slate-500">
                            <button class="reply-btn hover:text-indigo-600 transition-colors" data-comment-id="${comment.id}" data-user-name="${comment.user.name || 'User'}">
                                <i class="ri-reply-line mr-1"></i>Reply
                            </button>
                        </div>
                        
                        <div id="edit-form-${comment.id}" class="hidden mt-3">
                            <form class="edit-comment-form" data-comment-id="${comment.id}">
                                <textarea
                                    class="w-full p-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none text-sm"
                                    rows="3"
                                    required
                                >${comment.content}</textarea>
                                <div class="flex items-center justify-end space-x-2 mt-2">
                                    <button type="button" class="cancel-edit-btn px-3 py-1 text-sm text-slate-600 hover:text-slate-800 transition-colors">
                                        Cancel
                                    </button>
                                    <button type="submit" class="px-3 py-1 text-sm bg-indigo-600 text-white rounded hover:bg-indigo-700 transition-colors">
                                        Save
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            `;
            
            return div;
        }

        formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffInSeconds = Math.floor((now - date) / 1000);
            
            if (diffInSeconds < 60) return 'just now';
            if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m`;
            if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h`;
            if (diffInSeconds < 2592000) return `${Math.floor(diffInSeconds / 86400)}d`;
            
            return date.toLocaleDateString();
        }

        async submitComment() {
            if (!this.commentContent || !this.submitBtn) return;
            
            const content = this.commentContent.value.trim();
            if (!content || this.submitBtn.disabled) return;

            this.setLoadingState(true);

            try {
                const response = await fetch(`/posts/${this.currentPostId}/comments`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        content: content,
                        parent_id: this.parentCommentId
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Update comments count in modal and on page
                    this.updateCommentsCount(data.comments_count);
                    
                    // Reload comments to show the new comment
                    await this.loadComments(this.currentPostId);
                    
                    // Reset form
                    this.resetForm();
                } else {
                    throw new Error(data.message || 'Failed to post comment');
                }
            } catch (error) {
                console.error('Error posting comment:', error);
                alert('Failed to post comment. Please try again.');
            } finally {
                this.setLoadingState(false);
            }
        }

        startReply(commentId, userName) {
            this.isReplyMode = true;
            this.parentCommentId = commentId;
            if (this.parentCommentInput) this.parentCommentInput.value = commentId;
            if (this.replyToUser) this.replyToUser.textContent = userName;
            if (this.replyingTo) this.replyingTo.classList.remove('hidden');
            if (this.commentContent) {
                this.commentContent.placeholder = `Reply to ${userName}...`;
                this.commentContent.focus();
            }
            if (this.submitText) this.submitText.textContent = 'Reply';
        }

        cancelReplyMode() {
            this.isReplyMode = false;
            this.parentCommentId = null;
            if (this.parentCommentInput) this.parentCommentInput.value = '';
            if (this.replyingTo) this.replyingTo.classList.add('hidden');
            if (this.commentContent) this.commentContent.placeholder = 'Write a comment...';
            if (this.submitText) this.submitText.textContent = 'Comment';
        }

        updateCommentsCount(count) {
            // Update count on the main page
            const commentBtn = document.querySelector(`[data-post-id="${this.currentPostId}"].comment-btn`);
            if (commentBtn) {
                const countSpan = commentBtn.querySelector('.comments-count');
                if (countSpan) {
                    countSpan.textContent = count;
                }
            }
        }


        toggleSubmitButton() {
            if (this.commentContent && this.submitBtn) {
                const content = this.commentContent.value.trim();
                const isValid = content.length > 0 && content.length <= 3000;
                this.submitBtn.disabled = !isValid;
            }
        }

        setLoadingState(isLoading) {
            if (this.submitBtn && this.submitText && this.submitLoading) {
                this.submitBtn.disabled = isLoading;
                
                if (isLoading) {
                    this.submitText.classList.add('hidden');
                    this.submitLoading.classList.remove('hidden');
                } else {
                    this.submitText.classList.remove('hidden');
                    this.submitLoading.classList.add('hidden');
                }
            }
        }

        resetForm() {
            if (this.commentContent) {
                this.commentContent.value = '';
                this.toggleSubmitButton();
            }
            this.cancelReplyMode();
        }

        showError(message) {
            if (this.commentsList) {
                this.commentsList.innerHTML = `
                    <div class="text-center text-red-500 py-8">
                        <i class="ri-error-warning-line text-2xl mb-2"></i>
                        <p>${message}</p>
                    </div>
                `;
            }
        }
    }

    // Initialize comment manager
    window.commentManager = new CommentManager();

    // Alert System Functions
    function showAlert(type, message, title = null, id = null) {
        // Remove any existing alert with the same ID
        if (id) {
            const existingAlert = document.getElementById(id);
            if (existingAlert) {
                existingAlert.remove();
            }
        }
        
        // Create alert element
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert-component border rounded-lg p-4 mb-4 transition-all duration-300 ease-in-out transform`;
        
        // Set type-specific classes
        const typeClasses = {
            'info': 'bg-blue-50 border-blue-200 text-blue-800',
            'success': 'bg-green-50 border-green-200 text-green-800',
            'warning': 'bg-yellow-50 border-yellow-200 text-yellow-800',
            'error': 'bg-red-50 border-red-200 text-red-800'
        };
        
        const iconClasses = {
            'info': 'ri-information-line text-blue-500',
            'success': 'ri-check-line text-green-500',
            'warning': 'ri-error-warning-line text-yellow-500',
            'error': 'ri-close-circle-line text-red-500'
        };
        
        alertDiv.classList.add(...typeClasses[type].split(' '));
        
        if (id) {
            alertDiv.id = id;
        }
        
        alertDiv.innerHTML = `
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="${iconClasses[type]} text-lg"></i>
                </div>
                <div class="ml-3 flex-1">
                    ${title ? `<h3 class="text-sm font-medium mb-1">${title}</h3>` : ''}
                    <div class="text-sm">${message}</div>
                </div>
                <div class="ml-auto pl-3">
                    <div class="-mx-1.5 -my-1.5">
                        <button 
                            type="button" 
                            class="inline-flex rounded-md p-1.5 hover:bg-opacity-20 focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors"
                            onclick="dismissAlert(this)"
                        >
                            <span class="sr-only">Dismiss</span>
                            <i class="ri-close-line text-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        // Insert at the top of the alert container
        const alertContainer = document.getElementById('alert-container');
        if (alertContainer) {
            alertContainer.appendChild(alertDiv);
        } else {
            const mainContent = document.querySelector('main') || document.body;
            const firstChild = mainContent.firstChild;
            mainContent.insertBefore(alertDiv, firstChild);
        }
        
        // Show with animation
        setTimeout(() => {
            alertDiv.style.display = 'block';
            alertDiv.style.transform = 'translateY(0)';
            alertDiv.style.opacity = '1';
        }, 10);
        
        // Auto-dismiss after 5 seconds for success/info messages
        if (type === 'success' || type === 'info') {
            setTimeout(() => {
                dismissAlert(alertDiv.querySelector('button'));
            }, 5000);
        }
    }

    function dismissAlert(button) {
        const alertDiv = button.closest('.alert-component');
        if (alertDiv) {
            alertDiv.style.transform = 'translateY(-10px)';
            alertDiv.style.opacity = '0';
            setTimeout(() => {
                alertDiv.remove();
            }, 300);
        }
    }

    // Global error handler functions
    window.showError = function(message, title = 'Error') {
        showAlert('error', message, title);
    };

    window.showSuccess = function(message, title = 'Success') {
        showAlert('success', message, title);
    };

    window.showWarning = function(message, title = 'Warning') {
        showAlert('warning', message, title);
    };

    window.showInfo = function(message, title = 'Info') {
        showAlert('info', message, title);
    };

    // Delete post function
    window.deletePost = function(postId) {
        openConfirmationModal('delete-post-modal', 'Delete Post', 'Are you sure you want to delete this post? This action cannot be undone.', function() {
            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            formData.append('_method', 'DELETE');
            
            fetch(`/posts/${postId}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                if (response.ok) {
                    // Remove the post from the DOM
                    const postElement = document.querySelector(`[data-post-id="${postId}"]`)?.closest('article');
                    if (postElement) {
                        postElement.style.opacity = '0';
                        postElement.style.transform = 'translateY(-10px)';
                        setTimeout(() => postElement.remove(), 300);
                    }
                    // Reload the page to ensure consistency
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    alert('Failed to delete post. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to delete post. Please try again.');
            });
        });
    };
    
    // PhotoSwipe is handled by the main layout
</script>
@endpush





