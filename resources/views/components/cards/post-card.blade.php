@props([
    'post',
    'compact' => false
])

@php
    $cardClass = $compact 
        ? 'block w-full group bg-slate-50 border border-slate-100 rounded-lg p-4 hover:border-indigo-300 hover:bg-slate-100 transition-all cursor-pointer'
        : 'block w-full bg-white rounded-xl shadow-sm border border-slate-100 p-6 hover:border-indigo-300 transition-all cursor-pointer';
@endphp

<a href="{{ route('posts.show', $post) }}" class="{{ $cardClass }}">
    <div class="flex items-start space-x-3 w-full">
        <div class="flex-shrink-0">
            <x-avatar 
                :src="$post->user->avatar ?? null"
                :name="$post->user->name ?? 'User'"
                size="sm"
                :color="$post->user->avatar_color ?? null" />
        </div>
        
        <div class="flex-1 min-w-0">
            <div class="flex items-center space-x-2 mb-2">
                <span class="font-medium text-slate-800">{{ $post->user->name ?? 'User' }}</span>
                <span class="text-sm text-slate-500">•</span>
                <span class="text-sm text-slate-500">{{ $post->created_at->diffForHumans() }}</span>
            </div>
            
            @if($post->title)
                <h3 class="text-sm font-semibold text-slate-800 mb-2">{{ $post->title }}</h3>
            @endif
            
            <p class="text-slate-600 text-sm line-clamp-2 mb-3">
                {{ Str::limit($post->content, 120) }}
            </p>
            
            @if($compact)
                <div class="flex items-center space-x-4 text-xs text-slate-500">
                    <span class="flex items-center space-x-1">
                        <i class="ri-heart-line"></i>
                        <span>{{ $post->likes()->count() }}</span>
                    </span>
                    <span class="flex items-center space-x-1">
                        <i class="ri-chat-1-line"></i>
                        <span>{{ $post->comments()->count() }}</span>
                    </span>
                    @if($post->hashtags && count($post->hashtags) > 0)
                        <span class="flex items-center space-x-1">
                            <i class="ri-price-tag-line"></i>
                            <span>{{ count($post->hashtags) }}</span>
                        </span>
                    @endif
                </div>
            @else
                <div class="flex items-center space-x-4 mt-4 text-sm text-slate-500">
                    <button class="flex items-center space-x-1 hover:text-indigo-600">
                        <i class="ri-heart-line"></i>
                        <span>{{ $post->likes_count ?? 0 }}</span>
                    </button>
                    <button class="flex items-center space-x-1 hover:text-indigo-600">
                        <i class="ri-chat-1-line"></i>
                        <span>{{ $post->comments_count ?? 0 }}</span>
                    </button>
                    <button class="flex items-center space-x-1 hover:text-indigo-600">
                        <i class="ri-share-line"></i>
                        <span>Share</span>
                    </button>
                </div>
            @endif
        </div>
    </div>
</a>
