@extends('layouts.app')

@section('title', 'Post - People Of Data')

@section('content')
<div class="w-full max-w-3xl mx-auto px-4 py-6">
    <!-- Back Link -->
    <div class="mb-4">
        <a href="/home" class="text-indigo-600 hover:text-indigo-700 transition-colors flex items-center space-x-2">
            <i class="ri-arrow-left-line"></i>
            <span>Back to Feed</span>
        </a>
    </div>
    
    <!-- Post -->
    <x-post-card :post="$post" />
    
    <!-- Comments Section -->
    <div class="bg-white rounded-xl shadow-[0_8px_24px_rgba(0,0,0,0.04)] p-6 mt-6">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">Comments ({{ $post->comments->count() }})</h3>
        
        <!-- Comments List -->
        <div id="comments-container" class="space-y-4 mb-6">
            @foreach($post->comments as $comment)
                <div class="comment-item flex space-x-3" data-comment-id="{{ $comment->id }}">
                    <x-avatar 
                        :src="$comment->user->avatar ?? null"
                        :name="$comment->user->name ?? 'User'"
                        size="sm"
                        :color="$comment->user->avatar_color ?? null" />
                    
                    <div class="flex-1 min-w-0">
                        <div class="bg-slate-100 rounded-lg p-3">
                            <div class="flex items-center space-x-2 mb-1">
                                <h4 class="font-semibold text-sm text-slate-800 flex items-center">{{ $comment->user->name ?? 'Anonymous' }}<x-business-badge :user="$comment->user" /></h4>
                                <span class="text-xs text-slate-500">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-slate-700 whitespace-pre-line">{{ $comment->content }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Comment Form -->
        <div class="border-t border-slate-100 pt-6">
            <form id="comment-form" class="space-y-3">
                @csrf
                <input type="hidden" name="post_id" value="{{ $post->id }}">
                <input type="hidden" name="parent_id" id="parent-comment-id" value="">
                
                <div id="replying-to" class="hidden mb-2 p-2 bg-slate-100 rounded-lg text-sm">
                    <span class="text-slate-600">Replying to <strong id="reply-to-user"></strong></span>
                    <button type="button" id="cancel-reply" class="ml-2 text-slate-400 hover:text-slate-600">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
                
                <div class="flex space-x-3">
                    <x-avatar 
                        :src="auth()->user()->avatar ?? null"
                        :name="auth()->user()->name ?? 'User'"
                        size="sm"
                        :color="auth()->user()->avatar_color ?? null" />
                    <div class="flex-1">
                        <textarea
                            id="comment-content"
                            name="content"
                            placeholder="Write a comment..."
                            class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"
                            rows="3"
                            maxlength="3000"
                            required
                        ></textarea>
                        <div class="flex items-center justify-end mt-3">
                            <button
                                type="button"
                                id="cancel-comment"
                                class="px-4 py-2 text-slate-600 hover:text-slate-800 transition-colors hidden mr-2"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                id="submit-comment"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                disabled
                            >
                                <span id="submit-text">Comment</span>
                                <span id="submit-loading" class="hidden">
                                    <i class="ri-loader-4-line animate-spin"></i>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Include Confirmation Modal -->
<x-confirmation-modal id="delete-post-modal" />

@push('scripts')
<style>
    .like-btn:hover {
        color: #ef4444 !important;
    }
    .like-btn.text-red-500:hover {
        color: #dc2626 !important;
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
                // Redirect back to feed
                window.location.href = '{{ route("home") }}';
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

// Post interactions (like buttons and comment buttons) - SAME AS HOMEPAGE
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
    
    if (e.target.closest('.comment-btn')) {
        e.preventDefault();
        
        // Scroll to comment textarea with animation
        const textarea = document.getElementById('comment-content');
        if (textarea) {
            const offset = textarea.getBoundingClientRect().top + window.pageYOffset - 100;
            window.scrollTo({
                top: offset,
                behavior: 'smooth'
            });
            
            // Focus the textarea after scrolling starts
            setTimeout(() => {
                textarea.focus();
            }, 300);
        }
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const commentForm = document.getElementById('comment-form');
    const commentContent = document.getElementById('comment-content');
    const submitBtn = document.getElementById('submit-comment');
    const submitText = document.getElementById('submit-text');
    const submitLoading = document.getElementById('submit-loading');
    const parentInput = document.getElementById('parent-comment-id');
    const replyingTo = document.getElementById('replying-to');
    const replyToUser = document.getElementById('reply-to-user');
    const cancelReply = document.getElementById('cancel-reply');
    
    let isReplyMode = false;
    let parentCommentId = null;
    
    // Enable/disable submit button based on content
    commentContent.addEventListener('input', function() {
        submitBtn.disabled = !this.value.trim();
    });
    
    // Cancel reply
    if (cancelReply) {
        cancelReply.addEventListener('click', function() {
            isReplyMode = false;
            parentCommentId = null;
            parentInput.value = '';
            replyingTo.classList.add('hidden');
            commentContent.placeholder = 'Write a comment...';
            submitText.textContent = 'Comment';
        });
    }
    
    // Submit comment
    commentForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const content = commentContent.value.trim();
        if (!content || submitBtn.disabled) return;
        
        submitBtn.disabled = true;
        submitText.classList.add('hidden');
        submitLoading.classList.remove('hidden');
        
        try {
            const response = await fetch('{{ route("comments.store", $post) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    content: content,
                    parent_id: parentCommentId
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Reload the page to show the new comment
                window.location.reload();
            } else {
                throw new Error(data.message || 'Failed to post comment');
            }
        } catch (error) {
            console.error('Error posting comment:', error);
            alert('Failed to post comment. Please try again.');
        } finally {
            submitBtn.disabled = false;
            submitText.classList.remove('hidden');
            submitLoading.classList.add('hidden');
        }
    });
});
</script>
@endpush
@endsection

