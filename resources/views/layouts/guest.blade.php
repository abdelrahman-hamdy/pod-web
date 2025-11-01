<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'People Of Data') }} - @yield('title', 'Welcome')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- RemixIcon -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" rel="stylesheet" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#4f46e5',
                            700: '#3730a3',
                            800: '#312e81',
                            900: '#1e1b4b',
                        },
                    },
                },
            },
        };
    </script>
    <style>
        .btn-primary {
            @apply bg-primary-600 text-white px-6 py-2 rounded-lg hover:bg-primary-700 transition-all duration-200 font-medium;
        }
        .btn-secondary {
            @apply bg-white text-primary-600 border border-primary-600 px-6 py-2 rounded-lg hover:bg-primary-50 transition-all duration-200 font-medium;
        }
    </style>
    @livewireStyles

    <!-- Meta Tags -->
    <meta name="description" content="@yield('description', 'Join People Of Data - The premier community for data science and AI professionals in Egypt and MENA')">
    
    @stack('head')
</head>

<body class="font-sans text-gray-900 antialiased bg-gray-50">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <!-- Logo -->
                    <div class="flex items-center">
                        <a href="{{ route('index') }}" class="flex items-center space-x-3">
                            <div class="w-10 h-10 flex items-center justify-center">
                                <img src="{{ asset('storage/assets/pod-logo.png') }}" alt="Logo" class="w-full h-full object-contain">
                            </div>
                            <span class="text-2xl font-extrabold text-slate-900 tracking-tight">People Of Data</span>
                        </a>
                    </div>

                    <!-- Auth Links -->
                    <div class="flex items-center space-x-3">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ route('home') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium">
                                    <i class="ri-dashboard-line mr-2"></i>
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 text-indigo-600 hover:text-indigo-700 transition-colors font-medium">
                                    <i class="ri-login-box-line mr-2"></i>
                                    Log in
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium">
                                        <i class="ri-user-add-line mr-2"></i>
                                        Sign Up
                                    </a>
                                @endif
                            @endauth
                        @endif
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1">
            <!-- Flash Messages -->
            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)" 
                     class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 mx-4 mt-4">
                    <span class="block sm:inline">{{ session('success') }}</span>
                    <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                        <button @click="show = false" class="text-green-500">
                            <svg class="fill-current h-6 w-6" viewBox="0 0 20 20"><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                        </button>
                    </span>
                </div>
            @endif

            @if (session('error'))
                <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)" 
                     class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 mx-4 mt-4">
                    <span class="block sm:inline">{{ session('error') }}</span>
                    <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                        <button @click="show = false" class="text-red-500">
                            <svg class="fill-current h-6 w-6" viewBox="0 0 20 20"><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                        </button>
                    </span>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200">
            <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                <div class="text-center text-gray-500">
                    <p>&copy; {{ date('Y') }} People Of Data. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>

    @livewireScripts
    @stack('scripts')
    
    <!-- Back to Top Button -->
    <div id="back-to-top" class="fixed bottom-8 right-8 z-50 opacity-0 pointer-events-none transition-all duration-300 ease-in-out transform">
        <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" 
                class="bg-white text-slate-700 w-10 h-10 flex items-center justify-center rounded-full shadow-lg hover:bg-slate-50 transition-colors duration-200 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            <i class="ri-arrow-up-line text-lg"></i>
        </button>
    </div>
    
    <script>
        // Show/hide back to top button based on scroll position
        window.addEventListener('scroll', function() {
            const backToTopButton = document.getElementById('back-to-top');
            if (window.scrollY > 400) {
                backToTopButton.classList.remove('opacity-0', 'pointer-events-none');
                backToTopButton.classList.add('opacity-100');
            } else {
                backToTopButton.classList.add('opacity-0', 'pointer-events-none');
                backToTopButton.classList.remove('opacity-100');
            }
        });
    </script>
</body>
</html>
