@extends('layouts.guest')

@section('title', 'Forgot Password')
@section('description', 'Reset your People Of Data account password')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-slate-50 via-indigo-50 to-purple-50">
    <div class="max-w-md w-full">
        <!-- Logo and Title -->
        <div class="text-center mb-8">
            <div class="mx-auto w-20 h-20 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-full flex items-center justify-center mb-4">
                <i class="ri-lock-password-line text-4xl text-indigo-600"></i>
            </div>
            <h2 class="text-3xl font-bold text-slate-800 mb-2">
                Forgot Password?
            </h2>
            <p class="text-slate-600">
                No problem! Just enter your email address and we'll send you a password reset link.
            </p>
        </div>

        <!-- Card -->
        <div class="bg-white rounded-xl shadow-lg border border-slate-200 p-8">
            @if (session('status'))
                <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="ri-checkbox-circle-line text-green-600 text-xl mr-3"></i>
                        <div>
                            <p class="text-sm font-medium text-green-800">{{ session('status') }}</p>
                            <p class="text-xs text-green-700 mt-1">Please check your email for the reset link.</p>
                        </div>
                    </div>
                </div>
            @endif

            <form class="space-y-6" method="POST" action="{{ route('password.email') }}">
                @csrf

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-2">
                        Email address
                    </label>
                    <input id="email" name="email" type="email" autocomplete="email" required 
                           value="{{ old('email') }}"
                           placeholder="you@example.com"
                           class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors @error('email') border-red-300 @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" class="w-full bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition-all font-medium shadow-md hover:shadow-lg">
                        <i class="ri-mail-send-line mr-2"></i>
                        Send Reset Link
                    </button>
                </div>
            </form>

            <!-- Back to Login -->
            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-700 font-medium transition-colors">
                    <i class="ri-arrow-left-line mr-2"></i>
                    Back to Sign In
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
