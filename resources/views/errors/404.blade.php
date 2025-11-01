@extends('layouts.app')

@section('title', '404 - Page Not Found - People Of Data')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full text-center">
        <!-- 404 Number -->
        <div class="mb-8">
            <h1 class="text-9xl font-bold bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 bg-clip-text text-transparent">
                404
            </h1>
        </div>

        <!-- Message -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-slate-800 mb-4">
                Page Not Found
            </h2>
            <p class="text-lg text-slate-600 mb-2">
                Oops! The page you're looking for doesn't exist.
            </p>
            <p class="text-slate-500">
                It might have been moved, deleted, or you entered the wrong URL.
            </p>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('home') }}" 
               class="inline-flex items-center justify-center px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium">
                <i class="ri-home-line mr-2"></i>
                Go Home
            </a>
            <button onclick="window.history.back()" 
                    class="inline-flex items-center justify-center px-6 py-3 bg-white border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors font-medium">
                <i class="ri-arrow-left-line mr-2"></i>
                Go Back
            </button>
        </div>

        <!-- Quick Links -->
        <div class="mt-12 pt-8 border-t border-slate-200">
            <p class="text-sm text-slate-600 mb-4">Or explore these popular pages:</p>
            <div class="flex flex-wrap justify-center gap-3">
                <a href="{{ route('events.index') }}" 
                   class="inline-flex items-center px-4 py-2 text-sm text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 rounded-lg transition-colors">
                    <i class="ri-calendar-line mr-2"></i>
                    Events
                </a>
                <a href="{{ route('jobs.index') }}" 
                   class="inline-flex items-center px-4 py-2 text-sm text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 rounded-lg transition-colors">
                    <i class="ri-briefcase-line mr-2"></i>
                    Jobs
                </a>
                <a href="{{ route('hackathons.index') }}" 
                   class="inline-flex items-center px-4 py-2 text-sm text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 rounded-lg transition-colors">
                    <i class="ri-trophy-line mr-2"></i>
                    Hackathons
                </a>
                @auth
                    <a href="{{ route('posts.index') }}" 
                       class="inline-flex items-center px-4 py-2 text-sm text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 rounded-lg transition-colors">
                        <i class="ri-file-text-line mr-2"></i>
                        Posts
                    </a>
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection

