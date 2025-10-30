/**
 * Reusable post interactions script
 * Handles like, comment, delete, share, and lightbox functionality for posts
 */

(function() {
    'use strict';

    /**
     * Initialize post interactions
     */
    function initPostInteractions() {
        // Like button handler
        document.addEventListener('click', function(e) {
            if (e.target.closest('.like-btn')) {
                handleLike(e.target.closest('.like-btn'));
            }
        });

        // Comment button handler - only if comment modal exists
        document.addEventListener('click', function(e) {
            if (e.target.closest('.comment-btn') && typeof openCommentModal === 'function') {
                const button = e.target.closest('.comment-btn');
                const postId = button.dataset.postId;
                openCommentModal(postId);
            }
        });

        // Delete post handler
        window.deletePost = function(postId) {
            if (typeof openConfirmationModal === 'function') {
                openConfirmationModal('delete-post-modal', 'Delete Post', 
                    'Are you sure you want to delete this post? This action cannot be undone.', 
                    function() {
                        performDeletePost(postId);
                    });
            } else {
                if (confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
                    performDeletePost(postId);
                }
            }
        };

        // Post options toggle
        window.togglePostOptions = function(postId) {
            const menu = document.getElementById(`post-options-${postId}`);
            if (menu) {
                menu.classList.toggle('hidden');
            }
        };

        // Close post options when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.post-options-trigger') && !e.target.closest('[id^="post-options-"]')) {
                document.querySelectorAll('[id^="post-options-"]').forEach(menu => {
                    menu.classList.add('hidden');
                });
            }
        });
    }

    /**
     * Handle like button click
     */
    function handleLike(button) {
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
                if (countSpan) {
                    countSpan.textContent = data.likes_count;
                }
            }
        })
        .catch(error => console.error('Error:', error));
    }

    /**
     * Perform delete post operation
     */
    function performDeletePost(postId) {
        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        formData.append('_method', 'DELETE');

        fetch(`/posts/${postId}`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Find and remove the post element
                const postElement = document.querySelector(`[data-post-id="${postId}"]`);
                if (postElement) {
                    const article = postElement.closest('article');
                    if (article) {
                        article.style.opacity = '0';
                        article.style.transition = 'opacity 0.3s';
                        setTimeout(() => article.remove(), 300);
                    }
                }
                
                if (typeof showSuccessMessage === 'function') {
                    showSuccessMessage('Post deleted successfully');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof showErrorMessage === 'function') {
                showErrorMessage('Failed to delete post. Please try again.');
            }
        });
    }

    /**
     * Share modal functions
     */
    window.openShareModal = function(postId) {
        const modal = document.getElementById(`share-modal-${postId}`);
        if (modal) {
            modal.classList.remove('hidden');
        }
    };

    window.closeShareModal = function(postId) {
        const modal = document.getElementById(`share-modal-${postId}`);
        if (modal) {
            modal.classList.add('hidden');
            // Reset copy success message
            const successMsg = document.getElementById(`copy-success-${postId}`);
            if (successMsg) {
                successMsg.classList.add('hidden');
            }
        }
    };

    window.copyShareUrl = function(postId) {
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
    };

    /**
     * Lightbox functions
     */
    window.openLightbox = function(postId, imageIndex) {
        // Find the post element to get images
        const postElement = document.querySelector(`[data-post-id="${postId}"]`);
        if (!postElement) return;

        // Try to get images from data attribute or nearby element
        let imagesElement = postElement.closest('article').querySelector('[data-post-images]');
        if (!imagesElement) {
            imagesElement = postElement.closest('[data-post-images]');
        }

        if (!imagesElement) return;

        const images = JSON.parse(imagesElement.dataset.postImages || '[]');
        if (images.length === 0) return;

        // Create lightbox HTML
        createLightbox(postId, images, imageIndex);
    };

    function createLightbox(postId, images, currentIndex) {
        // Remove existing lightbox if any
        const existingLightbox = document.getElementById('post-lightbox');
        if (existingLightbox) {
            existingLightbox.remove();
        }

        const lightbox = document.createElement('div');
        lightbox.id = 'post-lightbox';
        lightbox.className = 'fixed inset-0 z-[100] flex items-center justify-center bg-black bg-opacity-90';
        lightbox.innerHTML = `
            <div class="relative max-w-5xl w-full px-4">
                <button onclick="closeLightbox()" 
                        class="absolute top-4 right-4 text-white hover:text-gray-300 transition-colors z-10">
                    <i class="ri-close-line text-4xl"></i>
                </button>
                
                ${images.length > 1 ? `
                    <button onclick="previousImage(${postId})" 
                            class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 transition-colors z-10">
                        <i class="ri-arrow-left-s-line text-4xl"></i>
                    </button>
                    <button onclick="nextImage(${postId})" 
                            class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 transition-colors z-10">
                        <i class="ri-arrow-right-s-line text-4xl"></i>
                    </button>
                ` : ''}
                
                <img src="/storage/${images[currentIndex]}" 
                     alt="Post image ${currentIndex + 1}" 
                     class="max-h-[90vh] w-auto mx-auto rounded-lg"
                     id="lightbox-image"
                     data-current-index="${currentIndex}">
                
                ${images.length > 1 ? `
                    <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 text-white text-sm">
                        ${currentIndex + 1} / ${images.length}
                    </div>
                ` : ''}
                
                <button onclick="downloadImage('${images[currentIndex]}')" 
                        class="absolute bottom-4 right-4 text-white hover:text-gray-300 transition-colors">
                    <i class="ri-download-line text-2xl"></i>
                </button>
            </div>
        `;

        document.body.appendChild(lightbox);

        // Store images and current index globally for navigation
        window.lightboxImages = images;
        window.lightboxPostId = postId;
    }

    window.closeLightbox = function() {
        const lightbox = document.getElementById('post-lightbox');
        if (lightbox) {
            lightbox.remove();
        }
        window.lightboxImages = null;
        window.lightboxPostId = null;
    };

    window.previousImage = function() {
        if (!window.lightboxImages) return;
        const img = document.getElementById('lightbox-image');
        if (!img) return;

        let currentIndex = parseInt(img.dataset.currentIndex);
        currentIndex = currentIndex > 0 ? currentIndex - 1 : window.lightboxImages.length - 1;
        updateLightboxImage(currentIndex);
    };

    window.nextImage = function() {
        if (!window.lightboxImages) return;
        const img = document.getElementById('lightbox-image');
        if (!img) return;

        let currentIndex = parseInt(img.dataset.currentIndex);
        currentIndex = currentIndex < window.lightboxImages.length - 1 ? currentIndex + 1 : 0;
        updateLightboxImage(currentIndex);
    };

    function updateLightboxImage(index) {
        const img = document.getElementById('lightbox-image');
        if (!img || !window.lightboxImages) return;

        img.src = `/storage/${window.lightboxImages[index]}`;
        img.dataset.currentIndex = index;

        // Update counter
        const counter = document.querySelector('#post-lightbox .absolute.bottom-4');
        if (counter) {
            counter.textContent = `${index + 1} / ${window.lightboxImages.length}`;
        }
    }

    window.downloadImage = function(imagePath) {
        const link = document.createElement('a');
        link.href = `/storage/${imagePath}`;
        link.download = imagePath.split('/').pop();
        link.click();
    };

    // Keyboard navigation for lightbox
    document.addEventListener('keydown', function(e) {
        const lightbox = document.getElementById('post-lightbox');
        if (!lightbox) return;

        if (e.key === 'Escape') {
            closeLightbox();
        } else if (e.key === 'ArrowLeft') {
            previousImage();
        } else if (e.key === 'ArrowRight') {
            nextImage();
        }
    });

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPostInteractions);
    } else {
        initPostInteractions();
    }
})();

