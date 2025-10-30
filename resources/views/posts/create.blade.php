@extends('layouts.app')

@section('title', 'Create Post')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Create a New Post
        </h2>
        <a href="{{ route('home') }}" class="btn-secondary">
            Back to Dashboard
        </a>
    </div>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- Author Info -->
                <div class="flex items-start space-x-4 mb-6">
                    <x-avatar 
                        :src="auth()->user()->avatar ?? null"
                        :name="auth()->user()->name ?? 'User'"
                        size="md"
                        :color="auth()->user()->avatar_color ?? null" />
                    <div>
                        <h3 class="font-medium text-gray-900">{{ auth()->user()->name }}</h3>
                        <p class="text-sm text-gray-500">{{ auth()->user()->role ?? 'Member' }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('posts.store') }}" class="space-y-6" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Post Content -->
                    <div>
                        <label for="content" class="block text-sm font-medium text-gray-700 mb-2">What's on your mind?</label>
                        <div class="relative">
                            <div id="hashtag-highlight" class="absolute inset-0 input-field border-transparent pointer-events-none overflow-hidden whitespace-pre-wrap break-words" aria-hidden="true"></div>
                            <textarea id="content" name="content" rows="6" 
                                class="input-field relative bg-transparent caret-slate-800" 
                                placeholder="Share your thoughts, insights, or ask a question to the community..." 
                                required style="color: transparent;"></textarea>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">Be respectful and constructive in your posts. Use #hashtags to categorize your content.</p>
                    </div>
                    
                    <!-- Post Type -->
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Post Type</label>
                        <select id="type" name="type" class="input-field">
                            <option value="general">General Discussion</option>
                            <option value="question">Question</option>
                            <option value="tutorial">Tutorial/Guide</option>
                            <option value="project">Project Showcase</option>
                            <option value="job">Job/Opportunity</option>
                            <option value="poll">Poll</option>
                            <option value="announcement">Announcement</option>
                        </select>
                    </div>
                    
                    <!-- Category -->
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select id="category_id" name="category_id" class="input-field">
                            <option value="">Select a category (optional)</option>
                            <option value="1">Data Science</option>
                            <option value="2">Machine Learning</option>
                            <option value="3">Data Analysis</option>
                            <option value="4">AI Engineering</option>
                            <option value="5">Programming</option>
                            <option value="6">Career</option>
                            <option value="7">Tools & Technologies</option>
                            <option value="8">Research</option>
                        </select>
                    </div>
                    
                    <!-- Tags -->
                    <div>
                        <label for="tags" class="block text-sm font-medium text-gray-700 mb-2">Tags</label>
                        <input type="text" id="tags" name="tags" 
                               class="input-field" 
                               placeholder="e.g. MachineLearning, Python, TensorFlow, DataScience">
                        <p class="text-sm text-gray-500 mt-1">Separate tags with commas. Use relevant hashtags to help others find your post.</p>
                    </div>
                    
                    <!-- Image Upload -->
                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-700 mb-2">Add Image (optional)</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="image" class="relative cursor-pointer bg-white rounded-md font-medium text-primary-600 hover:text-primary-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary-500">
                                        <span>Upload an image</span>
                                        <input id="image" name="image" type="file" class="sr-only" accept="image/*">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Link Preview (Optional) -->
                    <div>
                        <label for="link_url" class="block text-sm font-medium text-gray-700 mb-2">Add Link (optional)</label>
                        <input type="url" id="link_url" name="link_url" 
                               class="input-field" 
                               placeholder="https://example.com">
                        <p class="text-sm text-gray-500 mt-1">Share an interesting article, repository, or resource</p>
                    </div>
                    
                    <!-- Poll Options (conditional) -->
                    <div id="poll-options" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Poll Options</label>
                        <div class="space-y-2">
                            <input type="text" name="poll_option_1" class="input-field" placeholder="Option 1">
                            <input type="text" name="poll_option_2" class="input-field" placeholder="Option 2">
                            <input type="text" name="poll_option_3" class="input-field" placeholder="Option 3 (optional)">
                            <input type="text" name="poll_option_4" class="input-field" placeholder="Option 4 (optional)">
                        </div>
                        <div class="mt-3">
                            <label for="poll_duration" class="block text-sm font-medium text-gray-700 mb-1">Poll Duration</label>
                            <select id="poll_duration" name="poll_duration" class="input-field">
                                <option value="1">1 day</option>
                                <option value="3">3 days</option>
                                <option value="7" selected>1 week</option>
                                <option value="14">2 weeks</option>
                                <option value="30">1 month</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Post Settings -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-900 mb-3">Post Settings</h4>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <input id="allow_comments" name="allow_comments" type="checkbox" 
                                       class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded" checked>
                                <label for="allow_comments" class="ml-2 block text-sm text-gray-900">
                                    Allow comments
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input id="is_featured" name="is_featured" type="checkbox" 
                                       class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                <label for="is_featured" class="ml-2 block text-sm text-gray-900">
                                    Request to feature this post
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit Buttons -->
                    <div class="flex justify-between items-center pt-6">
                        <div class="flex items-center space-x-4">
                            <button type="button" id="save-draft" class="btn-secondary">
                                Save as Draft
                            </button>
                            <a href="{{ route('home') }}" class="text-gray-500 hover:text-gray-700">
                                Cancel
                            </a>
                        </div>
                        <button type="submit" class="btn-primary">
                            Publish Post
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Hashtag highlighting with overlay
const postContentTextarea = document.getElementById('content');
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

// Show/hide poll options based on post type
document.getElementById('type').addEventListener('change', function() {
    const pollOptions = document.getElementById('poll-options');
    if (this.value === 'poll') {
        pollOptions.classList.remove('hidden');
        // Make poll options required
        const pollInputs = pollOptions.querySelectorAll('input[name^="poll_option_"]');
        pollInputs[0].required = true;
        pollInputs[1].required = true;
    } else {
        pollOptions.classList.add('hidden');
        // Remove required attribute
        const pollInputs = pollOptions.querySelectorAll('input[name^="poll_option_"]');
        pollInputs.forEach(input => input.required = false);
    }
});

// File upload preview
document.getElementById('image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // You could add image preview functionality here
            console.log('Image selected:', file.name);
        };
        reader.readAsDataURL(file);
    }
});

// Save as draft functionality
document.getElementById('save-draft').addEventListener('click', function() {
    const form = document.querySelector('form');
    const draftInput = document.createElement('input');
    draftInput.type = 'hidden';
    draftInput.name = 'is_draft';
    draftInput.value = '1';
    form.appendChild(draftInput);
    form.submit();
});
</script>
@endsection
