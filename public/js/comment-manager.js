/**
 * Comment Modal Management - Reusable for dashboard and profile pages
 */

(function() {
    'use strict';

    class CommentManager {
        constructor() {
            this.modal = document.getElementById('comment-modal');
            this.backdrop = document.getElementById('comment-modal-backdrop');
            this.currentPostId = null;
            this.isReplyMode = false;
            this.parentCommentId = null;
            
            if (this.modal) {
                this.initializeElements();
                this.bindEvents();
            }
        }

        initializeElements() {
            this.modalPostContent = document.getElementById('modal-post-content');
            this.commentsList = document.getElementById('comments-list');
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
            
            // Clear comments from DOM to ensure animation runs on next open
            if (this.commentsList) {
                this.commentsList.innerHTML = '';
                // Add back the no-comments element
                this.commentsList.appendChild(this.noComments.cloneNode(true));
            }
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
                flattenedComments.forEach((comment, index) => {
                    const element = this.createCommentElement(comment, comment.level);
                    // Add initial hidden state
                    element.classList.add('comment-fade-in');
                    element.style.opacity = '0';
                    element.style.transform = 'translateY(10px)';
                    commentsContainer.appendChild(element);
                });
                
                this.commentsList.appendChild(commentsContainer);
                
                // Trigger animation for all comments using requestAnimationFrame
                requestAnimationFrame(() => {
                    const allComments = this.commentsList.querySelectorAll('.comment-fade-in');
                    allComments.forEach((element, index) => {
                        setTimeout(() => {
                            element.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                            element.style.opacity = '1';
                            element.style.transform = 'translateY(0)';
                            // Remove animation class after animation completes
                            setTimeout(() => {
                                element.classList.remove('comment-fade-in');
                            }, 300);
                        }, index * 50);
                    });
                });
            }
        }
        
        flattenComments(comments, level = 0, result = []) {
            comments.forEach(comment => {
                // Add the comment with its level
                const commentWithLevel = { ...comment, level };
                result.push(commentWithLevel);
                
                // If level is less than 2 (maximum 3 levels: 0, 1, 2), add replies
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
            
            const initials = (comment.user.name || 'U').substring(0, 2).toUpperCase();
            const avatarColor = comment.user.avatar_color || 'bg-slate-100 text-slate-600';
            const hasAvatar = comment.user.avatar && comment.user.avatar !== '' && comment.user.avatar !== 'null';
            
            const isOwner = window.currentUserId === comment.user_id;
            const isAdmin = window.currentUserId && (comment.user.role === 'admin' || comment.user.role === 'superadmin');
            
            div.innerHTML = `
                <div class="flex space-x-3">
                    <a href="/profile/${comment.user.id}" class="flex-shrink-0">
                        <div class="relative inline-flex items-center justify-center w-8 h-8 text-sm rounded-full ${avatarColor} font-medium overflow-hidden">
                            ${hasAvatar ? `
                                <img src="${comment.user.avatar}" alt="${comment.user.name || 'User'}" 
                                     class="w-full h-full object-cover rounded-full"
                                     onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="w-full h-full flex items-center justify-center rounded-full" style="display: none;">
                                    ${initials}
                                </div>
                            ` : `
                                <div class="w-full h-full flex items-center justify-center rounded-full">
                                    ${initials}
                                </div>
                            `}
                        </div>
                    </a>
                    
                    <div class="flex-1 min-w-0">
                        <div class="bg-slate-100 rounded-lg p-3">
                            <div class="flex items-center space-x-2 mb-1">
                                <a href="/profile/${comment.user.id}" class="hover:text-indigo-600 transition-colors">
                                    <h4 class="font-semibold text-sm text-slate-800 flex items-center">${comment.user.name || 'Anonymous'}</h4>
                                </a>
                                <span class="text-xs text-slate-500">${this.formatDate(comment.created_at)}</span>
                            </div>
                            <p class="text-sm text-slate-700 whitespace-pre-line">${comment.content}</p>
                        </div>
                        
                        <div class="flex items-center space-x-4 mt-2 text-xs text-slate-500">
                            <button class="reply-btn hover:text-indigo-600 transition-colors" data-comment-id="${comment.id}" data-user-name="${comment.user.name || 'User'}">
                                <i class="ri-reply-line mr-1"></i>Reply
                            </button>
                            ${isOwner || isAdmin ? `
                                <button class="edit-comment-btn hover:text-indigo-600 transition-colors" data-comment-id="${comment.id}">
                                    <i class="ri-pencil-line mr-1"></i>Edit
                                </button>
                                <button class="delete-comment-btn hover:text-red-600 transition-colors" data-comment-id="${comment.id}">
                                    <i class="ri-delete-bin-line mr-1"></i>Delete
                                </button>
                            ` : ''}
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

        async deleteComment(commentId) {
            if (!confirm('Are you sure you want to delete this comment?')) return;

            try {
                const response = await fetch(`/comments/${commentId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    // Reload comments
                    await this.loadComments(this.currentPostId);
                    this.updateCommentsCount(data.comments_count);
                } else {
                    throw new Error(data.message || 'Failed to delete comment');
                }
            } catch (error) {
                console.error('Error deleting comment:', error);
                alert('Failed to delete comment. Please try again.');
            }
        }

        startEdit(commentId) {
            const editForm = document.getElementById(`edit-form-${commentId}`);
            if (editForm) {
                editForm.classList.remove('hidden');
            }
        }

        cancelEdit(commentId) {
            const editForm = document.getElementById(`edit-form-${commentId}`);
            if (editForm) {
                editForm.classList.add('hidden');
            }
        }

        async updateComment(commentId, content) {
            try {
                const response = await fetch(`/comments/${commentId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ content: content })
                });

                const data = await response.json();

                if (data.success) {
                    // Reload comments to show updated comment
                    await this.loadComments(this.currentPostId);
                    this.cancelEdit(commentId);
                } else {
                    throw new Error(data.message || 'Failed to update comment');
                }
            } catch (error) {
                console.error('Error updating comment:', error);
                alert('Failed to update comment. Please try again.');
            }
        }
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            window.commentManager = new CommentManager();
            
            // Expose openCommentModal globally for post-interactions.js
            window.openCommentModal = function(postId) {
                if (window.commentManager) {
                    window.commentManager.openModal(postId);
                }
            };
        });
    } else {
        window.commentManager = new CommentManager();
        
        // Expose openCommentModal globally for post-interactions.js
        window.openCommentModal = function(postId) {
            if (window.commentManager) {
                window.commentManager.openModal(postId);
            }
        };
    }
})();

