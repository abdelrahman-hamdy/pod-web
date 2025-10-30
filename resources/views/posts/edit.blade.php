@extends('layouts.app')

@section('title', 'Edit Post - People Of Data')

@section('content')
<div class="w-full max-w-3xl mx-auto px-4 py-6">
    <!-- Back Link -->
    <div class="mb-4">
            <a href="{{ route('posts.show', $post) }}" class="text-indigo-600 hover:text-indigo-700 transition-colors flex items-center space-x-2">
            <i class="ri-arrow-left-line"></i>
            <span>Back to Post</span>
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-[0_8px_24px_rgba(0,0,0,0.04)] p-6">
        <!-- Post Preview Header -->
        <div class="border-b border-slate-200 pb-4 mb-6">
            <h2 class="text-xl font-semibold text-slate-800">Edit Post</h2>
            <p class="text-sm text-slate-500 mt-1">Make changes to your post below</p>
        </div>

        <!-- Author Info -->
        <div class="flex items-start space-x-4 mb-6">
            <x-avatar 
                :src="auth()->user()->avatar ?? null"
                :name="auth()->user()->name ?? 'User'"
                size="md"
                :color="auth()->user()->avatar_color ?? null" />
            <div>
                <h3 class="font-medium text-slate-900">{{ auth()->user()->name }}</h3>
                <p class="text-sm text-slate-500">Editing your post</p>
            </div>
        </div>

        <form method="POST" action="{{ route('posts.update', $post) }}" class="space-y-6" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <!-- Post Content -->
            <div>
                <label for="content" class="block text-sm font-medium text-slate-700 mb-2">Post Content</label>
                <div class="relative">
                    <div id="hashtag-highlight" class="absolute inset-0 w-full p-3 border border-transparent rounded-lg resize-none overflow-hidden whitespace-pre-wrap break-words" aria-hidden="true"></div>
                    <textarea id="content" name="content" rows="8" 
                        class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none relative bg-transparent caret-slate-800" 
                        placeholder="Share your thoughts..." style="color: transparent;">{{ old('content', $rawContent ?? ($post->content ?? '')) }}</textarea>
                </div>
                
            </div>
            
            <!-- Current Images -->
            @if($post->images && count($post->images) > 0)
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Current Images</label>
                    <div class="grid grid-cols-4 gap-4" id="existing-images-container">
                        @foreach($post->images as $index => $image)
                            <div class="relative group" data-image-index="{{ $index }}" data-image-path="{{ $image }}">
                                <img src="{{ Storage::url($image) }}" alt="Post image" class="w-full h-24 object-cover rounded-lg">
                                <button type="button" onclick="removeImagePreview('{{ $image }}', {{ $index }})" class="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <i class="ri-close-line text-xs"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <!-- Hidden container for images marked for removal -->
            <div id="remove-images-container"></div>
            
            <!-- Upload New Images -->
            <div>
                <label class="flex items-center space-x-2 text-slate-600 hover:text-indigo-600 transition-colors cursor-pointer inline-block">
                    <input type="file" id="images" name="images[]" multiple accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" class="hidden">
                    <div class="w-5 h-5 flex items-center justify-center">
                        <i class="ri-image-add-line"></i>
                    </div>
                    <span class="text-base">Add More Photos</span>
                </label>
                <p class="text-sm text-slate-500 mt-1 ml-7">You can upload up to 10 images (2MB each max)</p>
                <!-- Image Preview -->
                <div id="image-preview" class="mt-4 hidden">
                    <div class="flex flex-wrap gap-2" id="preview-container"></div>
                </div>
            </div>
            
            <input type="hidden" id="image-count" value="{{ count($post->images ?? []) }}">
            
            <!-- Submit Buttons -->
            <div class="flex justify-end space-x-3 pt-4 border-t border-slate-200">
                <a href="{{ route('posts.show', $post) }}" class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-slate-900 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
                    Update Post
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
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
        // Initial sync for existing content
        syncHighlight();
    }

    function removeImagePreview(imagePath, index) {
        const imageDiv = document.querySelector(`[data-image-index="${index}"]`);
        
        // Create a hidden input for this image to mark it for removal
        const removeContainer = document.getElementById('remove-images-container');
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'remove_images[]';
        hiddenInput.value = imagePath;
        hiddenInput.id = `remove_image_${index}`;
        removeContainer.appendChild(hiddenInput);
        
        // Visually hide the image
        imageDiv.style.opacity = '0.3';
        imageDiv.style.transform = 'scale(0.95)';
        imageDiv.setAttribute('data-marked-for-removal', 'true');
        setTimeout(() => {
            imageDiv.style.display = 'none';
        }, 300);
        
        updateImageCount();
    }
    
    function updateImageCount() {
        const visibleImages = document.querySelectorAll('.relative:not([style*="opacity: 0.3"])').length;
        document.getElementById('image-count').value = visibleImages;
    }
    
    // Image preview functionality
    const imagesInput = document.getElementById('images');
    const imagePreview = document.getElementById('image-preview');
    const previewContainer = document.getElementById('preview-container');
    
    if (imagesInput && imagePreview && previewContainer) {
        imagesInput.addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            
            files.forEach((file) => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const previewItem = document.createElement('div');
                        previewItem.className = 'relative inline-block';
                        
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'w-20 h-20 object-cover rounded-lg';
                        
                        const removeBtn = document.createElement('button');
                        removeBtn.type = 'button';
                        removeBtn.className = 'absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors';
                        removeBtn.innerHTML = '<i class="ri-close-line text-xs"></i>';
                        removeBtn.onclick = function() {
                            removeNewImage(previewItem, file);
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
            }
        });
    }
    
    function removeNewImage(previewItem, file) {
        previewItem.remove();
        
        // Remove file from input
        const dataTransfer = new DataTransfer();
        const input = document.getElementById('images');
        Array.from(input.files).forEach(f => {
            if (f !== file) {
                dataTransfer.items.add(f);
            }
        });
        input.files = dataTransfer.files;
        
        // Hide preview if no images left
        if (previewContainer.children.length === 0) {
            imagePreview.classList.add('hidden');
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // Validation: either content or images required
        const form = document.querySelector('form');
        const textarea = document.getElementById('content');
        const imagesInput = document.getElementById('images');
        
        form.addEventListener('submit', function(e) {
            const hasContent = textarea.value.trim() !== '';
            // Count visible existing images (not marked for removal)
            const existingImages = document.querySelectorAll('[data-image-index]:not([data-marked-for-removal="true"])');
            const hasExistingImages = existingImages.length > 0;
            const hasNewImages = imagesInput.files.length > 0;
            
            console.log('Form validation:', {
                hasContent,
                hasExistingImages,
                existingImagesCount: existingImages.length,
                hasNewImages,
                newImagesCount: imagesInput.files.length
            });
            
            if (!hasContent && !hasExistingImages && !hasNewImages) {
                e.preventDefault();
                alert('Post must have either content or at least one image.');
                return false;
            }
        });
    });
</script>
@endpush
@endsection

