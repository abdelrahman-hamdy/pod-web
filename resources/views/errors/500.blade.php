@extends('layouts.app')

@section('title', '500 - Server Error - People Of Data')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full text-center">
        <!-- 500 Number -->
        <div class="mb-8">
            <h1 class="text-9xl font-bold bg-gradient-to-r from-red-600 via-orange-600 to-yellow-600 bg-clip-text text-transparent">
                500
            </h1>
        </div>

        <!-- Message -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-slate-800 mb-4">
                Server Error
            </h2>
            <p class="text-lg text-slate-600 mb-2">
                Oops! Something went wrong on our end.
            </p>
            <p class="text-slate-500">
                We're working to fix this issue. Please try again in a few moments.
            </p>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('home') }}" 
               class="inline-flex items-center justify-center px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium">
                <i class="ri-home-line mr-2"></i>
                Go Home
            </a>
            <button onclick="window.location.reload()" 
                    class="inline-flex items-center justify-center px-6 py-3 bg-white border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors font-medium">
                <i class="ri-refresh-line mr-2"></i>
                Try Again
            </button>
        </div>

        <!-- Support Link -->
        <div class="mt-12 pt-8 border-t border-slate-200">
            <p class="text-sm text-slate-600">
                If this problem persists, please 
                <a href="mailto:support@peopleofdata.com" class="text-indigo-600 hover:text-indigo-700 font-medium">
                    contact support
                </a>
            </p>
        </div>
    </div>
</div>
@endsection

