@extends('layouts.app')

@section('title', 'Search Results - People Of Data')

@section('content')
<div class="w-full max-w-4xl mx-auto px-4 py-6">
    <!-- Search Header -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 mb-6">
        <h1 class="text-2xl font-bold text-slate-800 mb-2">Search Results</h1>
        <div class="flex items-center space-x-4">
            <input type="text" 
                   id="search-page-input" 
                   value="{{ $query }}" 
                   placeholder="Search posts and users..." 
                   class="flex-1 px-4 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
            <button onclick="performSearch()" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                Search
            </button>
        </div>
        <p class="text-sm text-slate-600 mt-4">
            <span class="font-semibold text-slate-800">{{ $posts->count() }}</span> posts and 
            <span class="font-semibold text-slate-800">{{ $users->count() }}</span> users found
        </p>
    </div>

    <!-- Results -->
    @if($posts->count() > 0 || $users->count() > 0)
        <!-- Users Section -->
        @if($users->count() > 0)
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 mb-6">
                <h2 class="text-lg font-semibold text-slate-800 mb-4 flex items-center space-x-2">
                    <i class="ri-user-line"></i>
                    <span>People ({{ $users->count() }})</span>
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($users as $user)
                        <a href="{{ route('profile.show.other', $user) }}" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-slate-50 transition-colors">
                            <x-avatar 
                                :src="$user->avatar ?? null"
                                :name="$user->name ?? 'User'"
                                size="md"
                                :color="$user->avatar_color ?? null" />
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-slate-800">{{ $user->name ?? 'User' }}</div>
                                @if($user->title)
                                    <div class="text-sm text-slate-600">{{ $user->title }}</div>
                                @endif
                                @if($user->city && $user->country)
                                    <div class="text-xs text-slate-500">{{ $user->city }}, {{ $user->country }}</div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Posts Section -->
        @if($posts->count() > 0)
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                <h2 class="text-lg font-semibold text-slate-800 mb-4 flex items-center space-x-2">
                    <i class="ri-file-text-line"></i>
                    <span>Posts ({{ $posts->count() }})</span>
                </h2>
                <div class="space-y-4">
                    @foreach($posts as $post)
                        @include('components.post-card', ['post' => $post])
                    @endforeach
                </div>
            </div>
        @endif
    @else
        <!-- No Results -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-12 text-center">
            <div class="text-6xl mb-4">🔍</div>
            <h3 class="text-xl font-semibold text-slate-800 mb-2">No results found</h3>
            <p class="text-slate-600 mb-6">Try searching with different keywords</p>
            <a href="{{ route('home') }}" class="inline-flex items-center space-x-2 px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                <i class="ri-home-line"></i>
                <span>Back to Home</span>
            </a>
        </div>
    @endif
</div>

<script>
    function performSearch() {
        const query = document.getElementById('search-page-input').value.trim();
        if (query.length >= 3) {
            window.location.href = `{{ route('search.results') }}?q=${encodeURIComponent(query)}`;
        }
    }

    // Allow Enter key to trigger search
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('search-page-input');
        if (searchInput) {
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });
        }
    });
</script>

@endsection

