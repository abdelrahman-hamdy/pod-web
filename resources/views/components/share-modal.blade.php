@props(['postId', 'postUrl'])

<!-- Share Modal -->
<div id="share-modal-{{ $postId }}" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50" onclick="closeShareModal({{ $postId }})"></div>
    
    <!-- Modal Content -->
    <div class="fixed inset-0 flex items-center justify-center pointer-events-none">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full mx-4 pointer-events-auto overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-800">Share Post</h3>
                    <button onclick="closeShareModal({{ $postId }})" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <i class="ri-close-line text-xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Share Buttons -->
            <div class="px-6 py-4">
                <div class="flex flex-col space-y-2">
                    <!-- Facebook -->
                    <a href="{{ route('posts.share-redirect', ['post' => $postId]) }}?platform=facebook&url={{ urlencode('https://www.facebook.com/sharer/sharer.php?u=' . $postUrl) }}" 
                       target="_blank"
                       class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-slate-50 transition-colors text-slate-700">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="ri-facebook-fill text-blue-600 text-lg"></i>
                        </div>
                        <span class="font-medium">Share on Facebook</span>
                    </a>
                    
                    <!-- Twitter/X -->
                    <a href="{{ route('posts.share-redirect', ['post' => $postId]) }}?platform=twitter&url={{ urlencode('https://twitter.com/intent/tweet?url=' . $postUrl) }}" 
                       target="_blank"
                       class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-slate-50 transition-colors text-slate-700">
                        <div class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="ri-twitter-x-fill text-slate-900 text-lg"></i>
                        </div>
                        <span class="font-medium">Share on X (Twitter)</span>
                    </a>
                    
                    <!-- LinkedIn -->
                    <a href="{{ route('posts.share-redirect', ['post' => $postId]) }}?platform=linkedin&url={{ urlencode('https://www.linkedin.com/sharing/share-offsite/?url=' . $postUrl) }}" 
                       target="_blank"
                       class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-slate-50 transition-colors text-slate-700">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="ri-linkedin-fill text-blue-600 text-lg"></i>
                        </div>
                        <span class="font-medium">Share on LinkedIn</span>
                    </a>
                    
                    <!-- WhatsApp -->
                    <a href="{{ route('posts.share-redirect', ['post' => $postId]) }}?platform=whatsapp&url={{ urlencode('https://wa.me/?text=' . $postUrl) }}" 
                       target="_blank"
                       class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-slate-50 transition-colors text-slate-700">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="ri-whatsapp-fill text-green-600 text-lg"></i>
                        </div>
                        <span class="font-medium">Share on WhatsApp</span>
                    </a>
                    
                    <!-- Email -->
                    <a href="{{ route('posts.share-redirect', ['post' => $postId]) }}?platform=email&url={{ urlencode('mailto:?subject=Shared from People Of Data&body=' . $postUrl) }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-slate-50 transition-colors text-slate-700">
                        <div class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="ri-mail-line text-slate-600 text-lg"></i>
                        </div>
                        <span class="font-medium">Share via Email</span>
                    </a>
                </div>
            </div>
            
            <!-- Copy Link Section -->
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
                <label class="block text-sm font-medium text-slate-700 mb-2">Copy link</label>
                <div class="flex items-center space-x-2">
                    <input type="text" 
                           id="share-url-{{ $postId }}" 
                           value="{{ $postUrl }}" 
                           readonly
                           class="flex-1 px-4 py-2 border border-slate-300 rounded-lg bg-white text-sm text-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <button onclick="copyShareUrl({{ $postId }}, '{{ route('posts.share-ping', ['post' => $postId]) }}')" 
                            id="copy-btn-{{ $postId }}"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium text-sm">
                        <i class="ri-file-copy-line"></i>
                    </button>
                </div>
                <p id="copy-success-{{ $postId }}" class="text-xs text-green-600 mt-2 hidden">
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

    function openShareModal(postId) {
        document.getElementById(`share-modal-${postId}`).classList.remove('hidden');
    }
    
    function closeShareModal(postId) {
        document.getElementById(`share-modal-${postId}`).classList.add('hidden');
        // Reset copy success message
        const successMsg = document.getElementById(`copy-success-${postId}`);
        if (successMsg) {
            successMsg.classList.add('hidden');
        }
    }
    
    function copyShareUrl(postId, pingUrl) {
        const urlInput = document.getElementById(`share-url-${postId}`);
        const copyBtn = document.getElementById(`copy-btn-${postId}`);
        const successMsg = document.getElementById(`copy-success-${postId}`);
        
        if (urlInput) {
            urlInput.select();
            document.execCommand('copy');
            
            // Show success message
            if (successMsg) {
                successMsg.classList.remove('hidden');
                setTimeout(() => {
                    successMsg.classList.add('hidden');
                }, 2000);
            }
            
            // Update button temporarily
            if (copyBtn) {
                const originalHTML = copyBtn.innerHTML;
                copyBtn.innerHTML = '<i class="ri-check-line"></i>';
                setTimeout(() => {
                    copyBtn.innerHTML = originalHTML;
                }, 1000);
            }
        }

        // Also count copy as a share via GET ping (no CSRF required)
        try {
            const url = `${pingUrl}?platform=copy&ts=${Date.now()}`;
            fetch(url, { method: 'GET', keepalive: true, credentials: 'same-origin' }).catch(() => {
                const img = new Image();
                img.src = url;
            });
        } catch (_) {}
    }

    function incrementShareCount(event, postId) {
        try {
            const link = event.currentTarget;
            const platform = link.getAttribute('data-platform') || null;
            const csrf = getCsrfToken();

            const payload = JSON.stringify({ platform });
            const url = `/posts/${postId}/share`;

            // Prefer fetch with keepalive
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
                body: payload,
                keepalive: true,
            }).catch(() => {
                // Fallback to sendBeacon without CSRF headers (server should ignore CSRF for beacons if configured), otherwise it's okay to fail silently
                try {
                    if (navigator.sendBeacon) {
                        const blob = new Blob([payload], { type: 'application/json' });
                        navigator.sendBeacon(url, blob);
                    }
                } catch (_) {}
            });
        } catch (_) {
            // Silent by design
        }
    }
</script>

