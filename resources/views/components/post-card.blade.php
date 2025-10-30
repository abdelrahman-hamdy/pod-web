@props(['post'])

@php
    use Illuminate\Support\Facades\Storage;
@endphp

<article class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 relative">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center space-x-3 flex-1">
            <a href="{{ route('profile.show.other', $post->user->id) }}" class="flex-shrink-0">
                <x-avatar 
                    :src="$post->user->avatar ?? null"
                    :name="$post->user->name ?? 'User'"
                    size="sm"
                    :color="$post->user->avatar_color ?? null" />
            </a>
            <div>
                <a href="{{ route('profile.show.other', $post->user->id) }}" class="hover:text-indigo-600 transition-colors flex items-center">
                    <h4 class="font-semibold text-slate-800">{{ $post->user->name ?? 'Anonymous' }}</h4>
                    <x-business-badge :user="$post->user" />
                </a>
            <p class="text-sm text-slate-500">{{ $post->user->job_title ?? 'Member' }} • 
                <a href="{{ route('posts.show', $post) }}" class="hover:text-indigo-600 transition-colors">{{ $post->created_at->diffForHumans() }}</a>
            </p>
            </div>
        </div>
        
        @auth
            @if($post->user_id === auth()->id())
                <div class="relative">
                    <button onclick="togglePostOptions({{ $post->id }})" class="post-options-trigger text-slate-400 hover:text-slate-600 transition-colors p-1">
                        <i class="ri-more-2-fill text-lg"></i>
                    </button>
                    
                    <div id="post-options-{{ $post->id }}" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-slate-200 z-10">
                        <div class="py-1">
                            <a href="{{ route('posts.edit', $post) }}" class="flex items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                <i class="ri-pencil-line mr-2"></i>
                                Edit
                            </a>
                            <button onclick="deletePost({{ $post->id }})" class="w-full text-left flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                <i class="ri-delete-bin-line mr-2"></i>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        @endauth
    </div>
    
    <p class="text-slate-700 mb-4 whitespace-pre-line">{!! $post->content !!}</p>
    
    @if($post->images && count($post->images) > 0)
        <div class="mb-4" data-post-images='@json($post->images)'>
            <div class="grid {{ count($post->images) === 1 ? 'grid-cols-1' : 'grid-cols-2' }} gap-2">
                @foreach(array_slice($post->images, 0, 4) as $index => $image)
                    <div class="relative w-full aspect-square cursor-pointer group" onclick="openLightbox({{ $post->id }}, {{ $index }})" data-post-id="{{ $post->id }}" data-image-index="{{ $index }}">
                        <img src="{{ Storage::url($image) }}" 
                             alt="Post image" 
                             class="w-full h-full object-cover group-hover:opacity-90 transition-opacity rounded-lg">
                        @if($index === 3 && count($post->images) > 4)
                            <div class="absolute inset-0 bg-black bg-opacity-60 flex items-center justify-center rounded-lg">
                                <div class="text-center text-white">
                                    <div class="text-4xl font-bold mb-1">+{{ count($post->images) - 4 }}</div>
                                    <div class="text-xl">more photos</div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
    
    <div class="-mx-6 -mb-6 border-t border-slate-100">
        <div class="grid grid-cols-3 divide-x divide-slate-100">
            <button class="like-btn flex items-center justify-center space-x-2 py-4 px-6 transition-colors {{ $post->is_liked ? 'text-red-500' : 'text-slate-500' }}" data-post-id="{{ $post->id }}" data-liked="{{ $post->is_liked ? 'true' : 'false' }}">
                <div class="w-5 h-5 flex items-center justify-center">
                    <i class="{{ $post->is_liked ? 'ri-heart-fill' : 'ri-heart-line' }}"></i>
                </div>
                <span class="text-sm">Like</span>
                <span class="text-sm likes-count">{{ $post->likes_count ?? 0 }}</span>
            </button>
            <button class="comment-btn flex items-center justify-center space-x-2 hover:text-indigo-600 transition-colors text-slate-500 py-4 px-6" data-post-id="{{ $post->id }}">
                <div class="w-5 h-5 flex items-center justify-center">
                    <i class="ri-chat-3-line"></i>
                </div>
                <span class="text-sm">Comment</span>
                <span class="text-sm comments-count">{{ $post->comments_count ?? 0 }}</span>
            </button>
            <button onclick="openShareModal({{ $post->id }})" class="flex items-center justify-center space-x-2 hover:text-indigo-600 transition-colors text-slate-500 py-4 px-6">
                <div class="w-5 h-5 flex items-center justify-center">
                    <i class="ri-share-line"></i>
                </div>
                <span class="text-sm">Share</span>
            </button>
        </div>
    </div>
</article>

<!-- Share Modal -->
<div id="share-modal-{{ $post->id }}" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50" onclick="closeShareModal({{ $post->id }})"></div>
    
    <!-- Modal Content -->
    <div class="fixed inset-0 flex items-center justify-center pointer-events-none">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full mx-4 pointer-events-auto overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-800">Share Post</h3>
                    <button onclick="closeShareModal({{ $post->id }})" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <i class="ri-close-line text-xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Share Buttons -->
            <div class="px-6 py-4">
                <div class="flex flex-wrap gap-2">
                    <!-- Facebook -->
                    <a href="{{ route('posts.share-redirect', ['post' => $post->id]) }}?platform=facebook&url={{ urlencode('https://www.facebook.com/sharer/sharer.php?u=' . route('posts.show', $post)) }}" 
                       target="_blank"
                       class="flex items-center space-x-2 px-4 py-2.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 transition-colors text-sm">
                        <i class="ri-facebook-fill text-blue-600"></i>
                        <span class="font-medium text-slate-700">Facebook</span>
                    </a>
                    
                    <!-- X -->
                    <a href="{{ route('posts.share-redirect', ['post' => $post->id]) }}?platform=twitter&url={{ urlencode('https://x.com/intent/post?url=' . route('posts.show', $post)) }}" 
                       target="_blank"
                       class="flex items-center space-x-2 px-4 py-2.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 transition-colors text-sm">
                        <i class="ri-twitter-x-fill text-slate-900"></i>
                        <span class="font-medium text-slate-700">X</span>
                    </a>
                    
                    <!-- LinkedIn -->
                    <a href="{{ route('posts.share-redirect', ['post' => $post->id]) }}?platform=linkedin&url={{ urlencode('https://www.linkedin.com/sharing/share-offsite/?url=' . route('posts.show', $post)) }}" 
                       target="_blank"
                       class="flex items-center space-x-2 px-4 py-2.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 transition-colors text-sm">
                        <i class="ri-linkedin-fill text-blue-600"></i>
                        <span class="font-medium text-slate-700">LinkedIn</span>
                    </a>
                    
                    <!-- WhatsApp -->
                    <a href="{{ route('posts.share-redirect', ['post' => $post->id]) }}?platform=whatsapp&url={{ urlencode('https://wa.me/?text=' . route('posts.show', $post)) }}" 
                       target="_blank"
                       class="flex items-center space-x-2 px-4 py-2.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 transition-colors text-sm">
                        <i class="ri-whatsapp-fill text-green-600"></i>
                        <span class="font-medium text-slate-700">WhatsApp</span>
                    </a>
                    
                    <!-- Telegram -->
                    <a href="{{ route('posts.share-redirect', ['post' => $post->id]) }}?platform=telegram&url={{ urlencode('https://t.me/share/url?url=' . route('posts.show', $post)) }}" 
                       target="_blank"
                       class="flex items-center space-x-2 px-4 py-2.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 transition-colors text-sm">
                        <i class="ri-telegram-fill text-blue-500"></i>
                        <span class="font-medium text-slate-700">Telegram</span>
                    </a>
                    
                    <!-- Email -->
                    <a href="{{ route('posts.share-redirect', ['post' => $post->id]) }}?platform=email&url={{ urlencode('mailto:?subject=Shared from People Of Data&body=' . route('posts.show', $post)) }}" 
                       class="flex items-center space-x-2 px-4 py-2.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 transition-colors text-sm">
                        <i class="ri-mail-line text-slate-600"></i>
                        <span class="font-medium text-slate-700">Email</span>
                    </a>
                </div>
            </div>
            
            <!-- Copy Link Section -->
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
                <label class="block text-sm font-medium text-slate-700 mb-2">Copy link</label>
                <div class="flex items-center space-x-2">
                    <input type="text" 
                           id="share-url-{{ $post->id }}" 
                           value="{{ route('posts.show', $post) }}" 
                           readonly
                           class="flex-1 px-4 py-2 border border-slate-300 rounded-lg bg-white text-sm text-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <button onclick="copyShareUrl({{ $post->id }}, '{{ route('posts.share-ping', ['post' => $post->id]) }}')" 
                            id="copy-btn-{{ $post->id }}"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium text-sm">
                        <i class="ri-file-copy-line"></i>
                    </button>
                </div>
                <p id="copy-success-{{ $post->id }}" class="text-xs text-green-600 mt-2 hidden">
                    <i class="ri-check-line"></i> Link copied to clipboard!
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    }

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta && meta.content) return meta.content;
        const xsrf = getCookie('XSRF-TOKEN');
        if (xsrf) try { return decodeURIComponent(xsrf); } catch (_) { return xsrf; }
        return '';
    }

    function copyShareUrl(postId, pingUrl) {
        const input = document.getElementById(`share-url-${postId}`);
        const successMsg = document.getElementById(`copy-success-${postId}`);
        const btn = document.getElementById(`copy-btn-${postId}`);

        if (!input) return;
        input.select();
        document.execCommand('copy');

        if (successMsg) {
            successMsg.classList.remove('hidden');
            setTimeout(() => successMsg.classList.add('hidden'), 1500);
        }

        // Silent share increment for copy via GET ping (no CSRF, most reliable)
        try {
            const url = `${pingUrl}?platform=copy&ts=${Date.now()}`;
            // Prefer fetch GET keepalive
            fetch(url, { method: 'GET', keepalive: true, credentials: 'same-origin' }).catch(() => {
                const img = new Image();
                img.src = url;
            });
        } catch (_) {}
    }
</script>
