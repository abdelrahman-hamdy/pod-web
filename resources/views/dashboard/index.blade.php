@extends('layouts.app')

@section('title', 'Newsfeed - People Of Data')

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
                    <div class="flex-1 min-w-0 relative">
                        <div id="hashtag-highlight" class="absolute inset-0 p-3 lg:p-4 border border-transparent rounded-lg pointer-events-none overflow-hidden whitespace-pre-wrap break-words text-sm lg:text-base" aria-hidden="true"></div>
                        <textarea name="content" id="post-content" placeholder="Share your thoughts with the community..."
                            class="w-full p-3 lg:p-4 border border-slate-200 rounded-lg resize-none focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm lg:text-base relative bg-transparent caret-slate-800"
                            rows="3" style="color: transparent;"></textarea>
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
                        class="bg-indigo-600 text-white px-4 lg:px-6 py-2 rounded-button hover:bg-indigo-700 transition-colors !rounded-button whitespace-nowrap text-sm lg:text-base w-full sm:w-auto opacity-75 cursor-not-allowed"
                        disabled>
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
                                            <span class="text-sm font-medium">
                                                @if($event->max_attendees)
                                                    {{ $event->getAvailableSpots() }} spots left
                                                @else
                                                    Unlimited spots
                                                @endif
                                            </span>
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
                                        <div class="text-sm font-semibold text-slate-800 flex items-center">{{ $trendingPost->user->name ?? 'User' }}<x-business-badge :user="$trendingPost->user" /></div>
                                        <div class="text-xs text-slate-500 mt-0.5">{{ $trendingPost->created_at->diffForHumans() }}</div>
                                    </div>
                                    <p class="text-sm text-slate-700 line-clamp-2">
                                        {{ Str::limit(strip_tags($trendingPost->content), 80) }}
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
                                <p class="text-sm font-medium text-slate-800 group-hover:text-indigo-600 transition-colors flex items-center">
                                    <span class="truncate">{{ $user->name }}</span><x-business-badge :user="$user" />
                                </p>
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
<!-- Comment Manager Script (must load before post-interactions) -->
<script src="{{ asset('js/comment-manager.js') }}"></script>
<!-- Post Interactions Script -->
<script src="{{ asset('js/post-interactions.js') }}"></script>
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
    
    // Post options toggle is handled by post-interactions.js
    
    document.addEventListener("DOMContentLoaded", function () {
        let currentOffset = {{ $posts->count() }};
        let isLoading = false;
        let hasMorePosts = true;

        // Hashtag highlighting with overlay
        const postContentTextarea = document.getElementById('post-content');
        const hashtagHighlight = document.getElementById('hashtag-highlight');

        function syncHighlight() {
            if (!postContentTextarea || !hashtagHighlight) return;

            const text = postContentTextarea.value;
            const highlightedText = text
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/(#\w+)/g, '<span style="color: #4f46e5; font-weight: 600;">$1</span>')
                .replace(/\n/g, '<br>');
            
            hashtagHighlight.innerHTML = highlightedText + ' '; 
            hashtagHighlight.scrollTop = postContentTextarea.scrollTop;
            hashtagHighlight.scrollLeft = postContentTextarea.scrollLeft;
        }

        if (postContentTextarea) {
            postContentTextarea.addEventListener('input', syncHighlight);
            postContentTextarea.addEventListener('scroll', syncHighlight);
        }

        function createPostCardHTML(post) {
            const postContentHtml = (post.content || '')
                .replace(/&/g, '&amp;')
                .replace(/\u003c/g, '&lt;')
                .replace(/\u003e/g, '&gt;')
                .replace(/(#\\w+)/g, '\u003cspan class="text-indigo-600 font-semibold"\u003e$1\u003c/span\u003e');

            const avatarHtml = post.user.avatar
                ? `\u003cimg src="${post.user.avatar}" alt="${post.user.name || 'User'}" class="w-8 h-8 rounded-full object-cover"\u003e`
                : `\u003cdiv class="w-8 h-8 ${post.user.avatar_color || 'bg-slate-100 text-slate-600'} rounded-full flex items-center justify-center"\u003e
                       \u003cspan class="font-semibold text-sm"\u003e${post.user.name ? post.user.name.substring(0, 2).toUpperCase() : 'U'}\u003c/span\u003e
                   \u003c/div\u003e`;

            const optionsMenu = post.user_id === window.currentUserId ? `
                <div class="relative">
                    <button onclick="togglePostOptions(${post.id})" class="text-slate-400 hover:text-slate-600 transition-colors p-1">
                        <i class="ri-more-2-fill text-lg"></i>
                    </button>
                    <div id="post-options-${post.id}" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-slate-200 z-10">
                        <div class="py-1">
                            <a href="/posts/${post.id}/edit" class="flex items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                <i class="ri-pencil-line mr-2"></i> Edit
                            </a>
                            <button onclick="deletePost(${post.id})" class="w-full text-left flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                <i class="ri-delete-bin-line mr-2"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>` : '';
            
            return `
                <article class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-3 flex-1">
                            <a href="/profile/${post.user.id}" class="flex-shrink-0">
                                ${avatarHtml}
                            </a>
                            <div>
                                <a href="/profile/${post.user.id}" class="hover:text-indigo-600 transition-colors flex items-center">
                                    <h4 class="font-semibold text-slate-800">${post.user.name}</h4>
                                </a>
                                <p class="text-sm text-slate-500">${post.user.job_title || 'Member'} • 
                                    <a href="/posts/${post.id}" class="hover:text-indigo-600 transition-colors">Just now</a>
                                </p>
                            </div>
                        </div>
                        ${optionsMenu}
                    </div>
                    <p class="text-slate-700 mb-4 whitespace-pre-line">${postContentHtml}</p>
                    ${post.images && post.images.length > 0 ? `
                    <div class="mb-4" data-post-images='${JSON.stringify(post.images)}'>
                        <div class="grid ${post.images.length === 1 ? 'grid-cols-1' : 'grid-cols-2'} gap-2">
                            ${post.images.slice(0, 4).map((image, index) => `
                                <div class="relative w-full aspect-square cursor-pointer group" onclick="openLightbox(${post.id}, ${index})" data-post-id="${post.id}" data-image-index="${index}">
                                    <img src="/storage/${image}" 
                                         alt="Post image" 
                                         class="w-full h-full object-cover group-hover:opacity-90 transition-opacity rounded-lg">
                                    ${index === 3 && post.images.length > 4 ? `
                                        <div class="absolute inset-0 bg-black bg-opacity-60 flex items-center justify-center rounded-lg">
                                            <div class="text-center text-white">
                                                <div class="text-4xl font-bold mb-1">+${post.images.length - 4}</div>
                                                <div class="text-xl">more photos</div>
                                            </div>
                                        </div>
                                    ` : ''}
                                </div>
                            `).join('')}
                        </div>
                    </div>
                ` : ''}
                    <div class="-mx-6 -mb-6 border-t border-slate-100">
                        <div class="grid grid-cols-3 divide-x divide-slate-100">
                            <button class="like-btn flex items-center justify-center space-x-2 py-4 px-6 transition-colors text-slate-500" data-post-id="${post.id}" data-liked="false">
                                <div class="w-5 h-5 flex items-center justify-center"><i class="ri-heart-line"></i></div>
                                <span class="text-sm">Like</span>
                                <span class="text-sm likes-count">0</span>
                            </button>
                            <button class="comment-btn flex items-center justify-center space-x-2 hover:text-indigo-600 transition-colors text-slate-500 py-4 px-6" data-post-id="${post.id}">
                                <div class="w-5 h-5 flex items-center justify-center"><i class="ri-chat-3-line"></i></div>
                                <span class="text-sm">Comment</span>
                                <span class="text-sm comments-count">0</span>
                            </button>
                            <button onclick="openShareModal(${post.id})" class="flex items-center justify-center space-x-2 hover:text-indigo-600 transition-colors text-slate-500 py-4 px-6">
                                <div class="w-5 h-5 flex items-center justify-center"><i class="ri-share-line"></i></div>
                                <span class="text-sm">Share</span>
                            </button>
                        </div>
                    </div>
                </article>
            `;
        }

        // Post form submission
        const postForm = document.getElementById('post-form');
        const submitBtn = document.getElementById('submit-post');
        const submitText = document.getElementById('submit-text');
        const submitLoading = document.getElementById('submit-loading');

        postForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (isLoading) return;

            // The hidden textarea is no longer used, so we directly get the value from the main textarea
            const postContentValue = document.getElementById('post-content').value.trim();
            if (postContentValue === '' && (selectedFiles.length === 0 || document.getElementById('post-images').files.length === 0)) {
                return;
            }
            
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

            // Lock button size to preserve width/height during loading
            const btnWidth = submitBtn.offsetWidth;
            const btnHeight = submitBtn.offsetHeight;
            submitBtn.style.width = btnWidth + 'px';
            submitBtn.style.height = btnHeight + 'px';

            submitText.classList.add('hidden');
            submitLoading.classList.remove('hidden');

            // Create FormData manually to have full control
            const formData = new FormData();
            
            // Add CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            formData.append('_token', csrfToken);
            
            // Add content field (empty string if no text)
            formData.append('content', postContentValue || '');
            
            // Add images if any
            const postImageInput = document.getElementById('post-images');
            // Ensure FileList reflects accumulated files
            rebuildFileInputFromSelected();
            for (let i = 0; i < postImageInput.files.length; i++) {
                formData.append('images[]', postImageInput.files[i]);
            }
            
            // Add required fields
            formData.append('type', 'text');
            formData.append('is_published', '1');

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
                
                if (data.success && data.post) {
                    // Add new post to the top of the feed with fade-in
                    const postsContainer = document.getElementById('posts-container');
                    const html = createPostHtml(data.post);
                    const wrapper = document.createElement('div');
                    wrapper.innerHTML = html.trim();
                    const newEl = wrapper.firstElementChild;
                    // start hidden and slightly translated
                    newEl.classList.add('opacity-0', 'translate-y-1', 'transition', 'duration-300', 'ease-out');
                    postsContainer.insertBefore(newEl, postsContainer.firstChild);
                    // trigger transition
                    requestAnimationFrame(() => {
                        newEl.classList.remove('opacity-0', 'translate-y-1');
                    });
                    
                    // Clear form
                    postForm.reset();
                    hashtagHighlight.innerHTML = ''; // Clear highlight div
                    document.getElementById('image-preview').classList.add('hidden');
                    document.getElementById('preview-container').innerHTML = '';
                    selectedFiles = [];
                    
                    // Re-validate button state after reset
                    validatePostButton();
                } else {
                    console.error('Server error:', data.message);
                    showError('Unable to create post. Please try again.', 'Post Error');
                }
            } catch (error) {
                console.error('Network error:', error);
                showError('Unable to create post. Please check your connection and try again.', 'Network Error');
            } finally {
                isLoading = false;
                // Re-enable/disable based on validity
                validatePostButton();
                submitText.classList.remove('hidden');
                submitLoading.classList.add('hidden');
                // Unlock button size
                submitBtn.style.width = '';
                submitBtn.style.height = '';
            }
        });

        // Image preview functionality
        const imageInput = document.getElementById('post-images');
        const imagePreview = document.getElementById('image-preview');
        const previewContainer = document.getElementById('preview-container');
        
        // Keep selected files across multiple selections
        let selectedFiles = [];

        function rebuildFileInputFromSelected() {
            const dt = new DataTransfer();
            selectedFiles.forEach(file => dt.items.add(file));
            imageInput.files = dt.files;
        }

        function renderPreviews() {
            previewContainer.innerHTML = '';
            if (selectedFiles.length === 0) {
                imagePreview.classList.add('hidden');
                return;
            }
            imagePreview.classList.remove('hidden');

            selectedFiles.forEach((file, index) => {
                if (!file.type.startsWith('image/')) return;
                const reader = new FileReader();
                reader.onload = function (ev) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'relative inline-block';
                    wrapper.dataset.index = String(index);

                    const img = document.createElement('img');
                    img.src = ev.target.result;
                    img.className = 'w-20 h-20 object-cover rounded-lg';

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors';
                    removeBtn.innerHTML = '<i class="ri-close-line text-xs"></i>';
                    removeBtn.addEventListener('click', function () {
                        removeImage(index);
                    });

                    wrapper.appendChild(img);
                    wrapper.appendChild(removeBtn);
                    previewContainer.appendChild(wrapper);
                };
                reader.readAsDataURL(file);
            });
        }

        // Render image previews when files are selected (accumulate instead of replace)
        if (imageInput && imagePreview && previewContainer) {
            imageInput.addEventListener('change', function (e) {
                const newFiles = Array.from(imageInput.files || []);
                if (newFiles.length === 0) {
                    // User canceled dialog; do nothing
                    validatePostButton();
                    return;
                }

                // Validate new files first
                const maxSize = 2 * 1024 * 1024; // 2MB
                const oversized = newFiles.filter(f => f.size > maxSize).map(f => f.name);
                if (oversized.length > 0) {
                    showError(`The following images are too large (max 2MB each): ${oversized.join(', ')}`, 'File Size Error');
                    // Do not add oversized files
                    // Keep previously selected files intact
                }

                // Merge valid new files
                const validNew = newFiles.filter(f => f.size <= maxSize);
                selectedFiles = selectedFiles.concat(validNew);

                // Enforce max count 10
                if (selectedFiles.length > 10) {
                    showError(`You can upload a maximum of 10 images per post. Please remove ${selectedFiles.length - 10} image(s).`, 'Too Many Images');
                    selectedFiles = selectedFiles.slice(0, 10);
                }

                // Rebuild input FileList and re-render
                rebuildFileInputFromSelected();
                renderPreviews();
                validatePostButton();

                // Clear the real input so selecting the same file again retriggers change
                imageInput.value = '';
            });
        }

        // Enable submit button only when there is text or at least one image
        function validatePostButton() {
            if (!submitBtn || !postContentTextarea || !imageInput) return;
            const hasText = postContentTextarea.value.trim() !== '';
            const hasImages = (selectedFiles && selectedFiles.length > 0) || imageInput.files.length > 0;
            const isValid = hasText || hasImages;
            submitBtn.disabled = !isValid || isLoading;
            if (!isValid) {
                submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
            } else {
                submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
            }
        }

        // Initialize and bind validation listeners
        validatePostButton();
        if (postContentTextarea) postContentTextarea.addEventListener('input', validatePostButton);
        if (imageInput) imageInput.addEventListener('change', validatePostButton);

        // Function to remove an image from preview
        function removeImage(index) {
            if (index < 0 || index >= selectedFiles.length) return;
            selectedFiles.splice(index, 1);
            rebuildFileInputFromSelected();
            renderPreviews();
            validatePostButton();
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
                
                const x = e.pageX - carousel.offsetLeft;
                const walk = (x - startX) * 2;
                
                // Only prevent default if user is actually trying to scroll horizontally
                // Check if horizontal movement is greater than vertical movement
                const deltaX = Math.abs(e.movementX);
                const deltaY = Math.abs(e.movementY);
                
                if (deltaX > deltaY) {
                    e.preventDefault();
                    carousel.scrollLeft = scrollLeft - walk;
                }
            });
        }

        // Post interactions are now handled by post-interactions.js

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
                    <button onclick="togglePostOptions(${post.id})" class="post-options-trigger text-slate-400 hover:text-slate-600 transition-colors p-1">
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
        
        // Use server-formatted HTML content (already linkified and hashtagged)
        const postContentHtml = post.content || '';
        
        return `
            <article class="bg-white rounded-xl shadow-[0_8px_24px_rgba(0,0,0,0.04)] p-6 relative">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3 flex-1">
                        ${avatarHtml}
                        <div>
                            <h4 class="font-semibold text-slate-800 flex items-center">${post.user.name || 'Anonymous'}${(post.user.role === 'client' || post.user.role === 'admin' || post.user.role === 'superadmin') ? (() => { const isAdmin = post.user.role === 'admin' || post.user.role === 'superadmin'; const tooltip = isAdmin ? 'Administrator' : 'Business Account'; return `<span class="inline-flex items-center justify-center w-4 h-4 bg-emerald-500 rounded-full flex-shrink-0 ml-1.5 badge-group relative" title="${tooltip}" aria-label="${tooltip}" onclick="event.stopPropagation();" onmouseenter="event.stopPropagation();"><i class="ri-check-line text-white text-xs leading-none"></i><span class="badge-tooltip absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-slate-900 text-white text-xs rounded whitespace-nowrap transition-opacity pointer-events-none z-50">${tooltip}<span class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1 border-4 border-transparent border-t-slate-900"></span></span></span>`; })() : ''}</h4>
                            <p class="text-sm text-slate-500">${post.user.job_title || 'Member'} • 
                                <a href="/posts/${post.id}" class="hover:text-indigo-600 transition-colors">${timeAgo}</a>
                            </p>
                        </div>
                    </div>
                    ${optionsMenu}
                </div>
                ${post.content ? `<p class="text-slate-700 mb-4 whitespace-pre-line">${postContentHtml}</p>` : ''}
                ${post.images && post.images.length > 0 ? `
                    <div class="mb-4" data-post-images='${JSON.stringify(post.images)}'>
                        ${post.images.length === 1 ? `
                            <div class="relative w-full rounded-lg overflow-hidden">
                                <img src="/storage/${post.images[0]}" 
                                     alt="Post image" 
                                     class="w-full h-auto object-contain cursor-pointer group-hover:opacity-90 transition-opacity"
                                     onclick="openLightbox(${post.id}, 0)"
                                     data-post-id="${post.id}"
                                     data-image-index="0">
                            </div>
                        ` : `
                            <div class="grid grid-cols-2 gap-2">
                                ${post.images.slice(0, 4).map((image, index) => `
                                    <div class="relative w-full aspect-square cursor-pointer group rounded-lg overflow-hidden" onclick="openLightbox(${post.id}, ${index})" data-post-id="${post.id}" data-image-index="${index}">
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
                        `}
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

    // Comment Modal Management is now handled by comment-manager.js

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

    // Delete post function is handled by post-interactions.js
    
    // PhotoSwipe is handled by the main layout
</script>
@endpush





