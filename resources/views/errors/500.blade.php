<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 - Server Error - People Of Data</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="font-sans text-gray-900 antialiased">
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
                <h2 class="text-3xl font-bold text-white mb-4">
                    Server Error
                </h2>
                <p class="text-lg text-white/90 mb-2">
                    Oops! Something went wrong on our end.
                </p>
                <p class="text-white/80">
                    We're working to fix this issue. Please try again in a few moments.
                </p>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('home') }}" 
                   class="inline-flex items-center justify-center px-6 py-3 bg-white text-indigo-600 rounded-lg hover:bg-gray-100 transition-colors font-medium shadow-lg">
                    <i class="ri-home-line mr-2"></i>
                    Go Home
                </a>
                <button onclick="window.location.reload()" 
                        class="inline-flex items-center justify-center px-6 py-3 bg-white/10 backdrop-blur-sm border-2 border-white/30 text-white rounded-lg hover:bg-white/20 transition-colors font-medium">
                    <i class="ri-refresh-line mr-2"></i>
                    Try Again
                </button>
            </div>

            <!-- Support Link -->
            <div class="mt-12 pt-8 border-t border-white/20">
                <p class="text-sm text-white/80">
                    If this problem persists, please 
                    <a href="mailto:support@peopleofdata.com" class="text-white hover:text-white/90 font-medium underline">
                        contact support
                    </a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>

