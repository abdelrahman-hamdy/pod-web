@props(['post' => null])

<!-- Comment Modal -->
<div id="comment-modal" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop - Click to close -->
    <div id="comment-modal-backdrop" class="fixed inset-0 bg-black bg-opacity-50"></div>
    
    <!-- Scrollable Content -->
    <div id="comment-modal-content" class="fixed inset-0 overflow-y-auto pointer-events-none">
        <div class="pointer-events-auto max-w-2xl mx-auto min-h-screen py-8">
            <!-- Main Container - Centered Content -->
            <div class="flex flex-col rounded-2xl overflow-hidden">
                <!-- Post Card (Full Component) -->
                <div id="modal-post-content" class="bg-white border-b border-slate-200">
                    <!-- Full post card will be inserted here -->
                </div>

                <!-- Comments Section -->
                <div class="bg-white">
                    <!-- Comments Header -->
                    <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
                        <h3 class="text-lg font-semibold text-slate-800">Comments</h3>
                    </div>

                    <!-- Comments List -->
                    <div id="comments-list" class="p-6 space-y-4 border-b border-slate-200">
                        <div class="text-center text-slate-500 py-8 hidden" id="no-comments">
                            <i class="ri-chat-3-line text-2xl mb-2"></i>
                            <p>No comments yet. Be the first to comment!</p>
                        </div>
                    </div>

                    <!-- Comment Form -->
                    <div class="p-6 bg-white">
                        <form id="comment-form" class="space-y-4">
                            <input type="hidden" id="post-id" value="">
                            <input type="hidden" id="parent-comment-id" value="">
                            
                            <div class="flex space-x-3">
                                <x-avatar 
                                    :src="auth()->user()->avatar ?? null"
                                    :name="auth()->user()->name ?? 'User'"
                                    size="sm"
                                    :color="auth()->user()->avatar_color ?? null" />
                                <div class="flex-1">
                                    <div id="replying-to" class="hidden mb-2 p-2 bg-slate-100 rounded-lg text-sm">
                                        <span class="text-slate-600">Replying to <strong id="reply-to-user"></strong></span>
                                        <button type="button" id="cancel-reply" class="ml-2 text-slate-400 hover:text-slate-600">
                                            <i class="ri-close-line"></i>
                                        </button>
                                    </div>
                                    <textarea
                                        id="comment-content"
                                        placeholder="Write a comment..."
                                        class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"
                                        rows="3"
                                        maxlength="3000"
                                        required
                                    ></textarea>
                                    <div class="flex items-center justify-end mt-3">
                                        <div class="flex items-center space-x-3">
                                            <button
                                                type="button"
                                                id="cancel-comment"
                                                class="px-4 py-2 text-slate-600 hover:text-slate-800 transition-colors hidden"
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
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

