<!DOCTYPE html>
<html lang="en">
@php
    $isCollapsed = request()->cookie('sidebar_collapsed', 'false') === 'true';
    $mainContentClass = $isCollapsed ? 'main-content-collapsed' : 'main-content-expanded';
@endphp

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'People Of Data - Community Hub')</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" rel="stylesheet" />
    <!-- GLightbox CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox@3/dist/css/glightbox.min.css">
    <style>
        :where([class^="ri-"])::before {
            content: "\f3c2";
        }
        
        /* Remove elastic scroll effect and prevent horizontal overflow */
        html, body {
            overscroll-behavior: none;
            -webkit-overflow-scrolling: touch;
            overflow-x: hidden;
            max-width: 100vw;
        }
        
        * {
            overscroll-behavior: none;
            box-sizing: border-box;
        }
        
        /* Ensure all containers respect viewport width */
        .w-full {
            max-width: 100%;
        }
        
        /* Sidebar transitions */
        .sidebar-collapsed {
            width: 80px;
        }
        
        .sidebar-expanded {
            width: 256px;
        }
        
        .main-content-collapsed {
            margin-left: 80px;
            transition: margin-left 0.3s ease;
        }
        
        .main-content-expanded {
            margin-left: 256px;
            transition: margin-left 0.3s ease;
        }
        
        .sidebar-text {
            transition: opacity 0.2s ease, transform 0.2s ease;
            transform-origin: left center;
        }
        
        .sidebar-collapsed .sidebar-text {
            opacity: 0;
            transform: translateX(-20px);
            visibility: hidden;
        }
        
        .sidebar-expanded .sidebar-text {
            opacity: 1;
            transform: translateX(0);
            visibility: visible;
        }
        
        /* When collapsed, center the icons */
        .sidebar-collapsed nav li a {
            padding-left: 0;
            padding-right: 0;
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
            height: 48px;
        }
        
        /* Make icons bigger when collapsed */
        .sidebar-collapsed nav li a div {
            width: 3rem;
            height: 2rem;
            margin: 0;
        }
        
        .sidebar-collapsed nav li a i {
            font-size: 1.3rem;
        }
        
        /* Business badge tooltip */
        .badge-group {
            position: relative;
        }
        
        .badge-group:hover .badge-tooltip {
            opacity: 1 !important;
        }
        
        .badge-tooltip {
            opacity: 0;
            pointer-events: none;
        }
        
        .sidebar-collapsed .mt-auto > div {
            padding: 0.5rem;
        }
        
        .sidebar-collapsed .mt-auto .bg-gradient-to-r {
            padding: 0.75rem;
        }
        
        /* Ensure smooth transitions */
        .sidebar {
            transition: all 0.3s ease;
        }
        
        /* Mobile sidebar styles */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                z-index: 60;
            }
            
            .sidebar.mobile-open {
                transform: translateX(0);
            }
            
            .main-content-collapsed,
            .main-content-expanded {
                margin-left: 0;
            }
            
            /* Hide desktop sidebar toggle button on mobile */
            #sidebar-toggle {
                display: none;
            }
        }
        
        /* Mobile overlay */
        .mobile-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
            z-index: 1;
        }
        
        .mobile-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        /* Tooltip for collapsed state */
        .sidebar-collapsed [title] {
            position: relative;
        }
        
        .sidebar-collapsed [title]:hover::after {
            content: attr(title);
            position: absolute;
            left: calc(100% + 0.5rem);
            top: 50%;
            transform: translateY(-50%);
            background: #1f2937;
            color: white;
            padding: 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            white-space: nowrap;
            z-index: 1000;
            pointer-events: none;
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: "#6366f1",
                        secondary: "#8b5cf6",
                    },
                    borderRadius: {
                        none: "0px",
                        sm: "4px",
                        DEFAULT: "8px",
                        md: "12px",
                        lg: "16px",
                        xl: "20px",
                        "2xl": "24px",
                        "3xl": "32px",
                        full: "9999px",
                        button: "8px",
                    },
                },
            },
        };
    </script>
    @stack('styles')
    
    <style>
        /* Animated Background Spots */
        .animated-spot {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            filter: blur(80px);
            opacity: 0.06;
            z-index: 0;
        }

        .spot1 {
            width: 900px;
            height: 900px;
            left: -10%;
            top: -20%;
            background: radial-gradient(circle at 60% 40%, #7c3aed 0%, #38bdf8 60%, transparent 100%);
            animation: spot1move 14s ease-in-out infinite;
        }

        .spot2 {
            width: 800px;
            height: 800px;
            right: -15%;
            top: 25%;
            background: radial-gradient(circle at 40% 60%, #f472b6 0%, #a5b4fc 70%, transparent 100%);
            animation: spot2move 16s ease-in-out infinite;
        }

        .spot3 {
            width: 1100px;
            height: 1100px;
            left: 35%;
            bottom: -30%;
            background: radial-gradient(circle at 50% 50%, #fef08a 0%, #f0abfc 60%, transparent 100%);
            animation: spot3move 18s ease-in-out infinite;
        }

        .spot4 {
            width: 700px;
            height: 700px;
            right: 10%;
            bottom: -20%;
            background: radial-gradient(circle at 60% 60%, #34d399 0%, #a7f3d0 70%, transparent 100%);
            animation: spot4move 20s ease-in-out infinite;
        }

        @keyframes spot1move {
            0%, 100% {
                transform: translateY(0) scale(1);
                opacity: 0.10;
            }
            40% {
                transform: translateY(60px) scale(1.12);
                opacity: 0.18;
            }
            70% {
                transform: translateY(-40px) scale(0.95);
                opacity: 0.06;
            }
        }

        @keyframes spot2move {
            0%, 100% {
                transform: translateY(0) scale(1);
                opacity: 0.10;
            }
            30% {
                transform: translateY(-70px) scale(1.13);
                opacity: 0.06;
            }
            60% {
                transform: translateY(50px) scale(0.92);
                opacity: 0.14;
            }
        }

        @keyframes spot3move {
            0%, 100% {
                transform: translateX(0) scale(1);
                opacity: 0.10;
            }
            50% {
                transform: translateX(-80px) scale(1.09);
                opacity: 0.06;
            }
            80% {
                transform: translateX(60px) scale(0.93);
                opacity: 0.14;
            }
        }

        @keyframes spot4move {
            0%, 100% {
                transform: translateX(0) scale(1);
                opacity: 0.10;
            }
            40% {
                transform: translateX(60px) scale(1.08);
                opacity: 0.06;
            }
            70% {
                transform: translateX(-40px) scale(0.95);
                opacity: 0.14;
            }
        }
    </style>
</head>

<body class="bg-white min-h-screen relative overflow-hidden">
    <!-- Modern animated circular gradient spots background -->
    <div class="fixed inset-0 -z-10">
        <div class="animated-spot spot1"></div>
        <div class="animated-spot spot2"></div>
        <div class="animated-spot spot3"></div>
        <div class="animated-spot spot4"></div>
    </div>
    
    <!-- Header -->
    @include('components.header')
    
    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobile-overlay" onclick="closeMobileSidebar()"></div>
    
    <!-- PhotoSwipe will be initialized dynamically -->
    
    <div class="flex min-h-screen pt-16 relative z-999">
        <!-- Sidebar -->
        @include('components.sidebar')
        
        <!-- Main Content -->
        <main class="flex-1 main-content {{ $mainContentClass }} min-h-[calc(100vh-4rem)] flex flex-col w-full min-w-0" id="main-content">
            <div class="flex-1 w-full min-w-0">
                <div class="max-w-6xl mx-auto px-6 py-8 w-full min-w-0">
                    @yield('content')
                </div>
            </div>
            
            <!-- Footer -->
            @include('components.footer')
        </main>
    </div>

    <script>
        // Mobile search toggle functionality
        function toggleMobileSearch() {
            const searchBar = document.getElementById('mobile-search');
            if (searchBar.classList.contains('hidden')) {
                searchBar.classList.remove('hidden');
                searchBar.querySelector('input').focus();
            } else {
                searchBar.classList.add('hidden');
            }
        }

        function closeMobileSearch() {
            const searchBar = document.getElementById('mobile-search');
            searchBar.classList.add('hidden');
        }

        // Mobile sidebar toggle functionality
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobile-overlay');
            
            if (sidebar.classList.contains('mobile-open')) {
                closeMobileSidebar();
            } else {
                openMobileSidebar();
            }
        }
        
        function openMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobile-overlay');
            
            // Force sidebar to be expanded on mobile
            sidebar.classList.remove('sidebar-collapsed');
            sidebar.classList.add('sidebar-expanded');
            
            sidebar.classList.add('mobile-open');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        }
        
        function closeMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobile-overlay');
            
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('active');
            document.body.style.overflow = ''; // Restore scrolling
        }

        // Helper function to set cookie
        function setCookie(name, value, days) {
            let expires = "";
            if (days) {
                let date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "") + expires + "; path=/";
        }

        // Sidebar toggle functionality (desktop only)
        function toggleSidebar() {
            // Only work on desktop (screen width > 768px)
            if (window.innerWidth <= 768) {
                return;
            }
            
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const toggleBtn = document.getElementById('sidebar-toggle');
            
            if (sidebar.classList.contains('sidebar-expanded')) {
                // Collapse sidebar
                sidebar.classList.remove('sidebar-expanded');
                sidebar.classList.add('sidebar-collapsed');
                mainContent.classList.remove('main-content-expanded');
                mainContent.classList.add('main-content-collapsed');
                toggleBtn.innerHTML = '<i class="ri-arrow-right-line text-sm"></i>';
                
                // Save state to cookie
                setCookie('sidebar_collapsed', 'true', 365);
            } else {
                // Expand sidebar
                sidebar.classList.remove('sidebar-collapsed');
                sidebar.classList.add('sidebar-expanded');
                mainContent.classList.remove('main-content-collapsed');
                mainContent.classList.add('main-content-expanded');
                toggleBtn.innerHTML = '<i class="ri-menu-line text-sm"></i>';
                
                // Save state to cookie
                setCookie('sidebar_collapsed', 'false', 365);
            }
        }

        // Handle window resize
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobile-overlay');
            
            // If screen becomes desktop size, close mobile sidebar
            if (window.innerWidth > 768) {
                sidebar.classList.remove('mobile-open');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            } else {
                // If screen becomes mobile size, ensure sidebar is expanded
                sidebar.classList.remove('sidebar-collapsed');
                sidebar.classList.add('sidebar-expanded');
            }
        });

        // Profile dropdown functionality
        document.addEventListener("DOMContentLoaded", function () {
            const profileMenu = document.getElementById("profile-menu");
            const profileDropdown = document.getElementById("profile-dropdown");
            
            if (profileMenu && profileDropdown) {
                profileMenu.addEventListener("click", function (e) {
                    e.stopPropagation();
                    profileDropdown.classList.toggle("hidden");
                });
                
                document.addEventListener("click", function () {
                    if (!profileDropdown.classList.contains("hidden")) {
                        profileDropdown.classList.add("hidden");
                    }
                });
            }
        });

        // GLightbox functionality
        let lightbox = null;

        function openLightbox(postId, imageIndex) {
            // Find the post data
            const postElement = document.querySelector(`[data-post-id="${postId}"]`).closest('article');
            if (!postElement) return;

            // Get the full image list from the data attribute
            const imagesContainer = postElement.querySelector('[data-post-images]');
            if (!imagesContainer) return;
            
            const imagePaths = JSON.parse(imagesContainer.getAttribute('data-post-images'));
            
            // Use GLightbox if available
            if (typeof GLightbox !== 'undefined') {
                try {
                    // Create temporary gallery for this post
                    const tempGallery = document.createElement('div');
                    tempGallery.className = 'post-images-gallery';
                    tempGallery.id = `temp-gallery-${postId}`;
                    tempGallery.style.display = 'none';
                    
                    // Create image elements
                    imagePaths.forEach((path, index) => {
                        const link = document.createElement('a');
                        link.href = `/storage/${path}`;
                        link.setAttribute('data-glightbox', 'description: Post Image');
                        tempGallery.appendChild(link);
                    });
                    
                    document.body.appendChild(tempGallery);
                    
                    // Create array of image items for GLightbox
                    const items = imagePaths.map(path => ({
                        href: `/storage/${path}`,
                        type: 'image'
                    }));
                    
                    // Create temporary GLightbox instance
                    const tempLightbox = GLightbox({
                        elements: items,
                        touchNavigation: true,
                        loop: true,
                        autoplayVideos: false,
                        startAt: imageIndex
                    });
                    
                    // Open the lightbox
                    tempLightbox.open();
                    
                    // Clean up after close
                    tempLightbox.on('close', () => {
                        setTimeout(() => {
                            if (document.getElementById(`temp-gallery-${postId}`)) {
                                document.body.removeChild(tempGallery);
                            }
                            tempLightbox.destroy();
                        }, 100);
                    });
                } catch (error) {
                    console.error('Error opening GLightbox:', error);
                    // Fallback to enhanced lightbox
                    showFallbackLightbox(postId, imageIndex);
                }
            } else {
                // Fallback to enhanced lightbox
                showFallbackLightbox(postId, imageIndex);
            }
        }

        // Enhanced fallback lightbox with navigation
        function showFallbackLightbox(postId, imageIndex) {
            const postElement = document.querySelector(`[data-post-id="${postId}"]`).closest('article');
            if (!postElement) return;

            const imagesContainer = postElement.querySelector('[data-post-images]');
            if (!imagesContainer) return;
            
            const imagePaths = JSON.parse(imagesContainer.getAttribute('data-post-images'));
            let currentIndex = imageIndex;
            
            // Create enhanced modal
            const modal = document.createElement('div');
            modal.id = 'fallback-lightbox';
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.7);
                z-index: 9999;
                display: flex;
                flex-direction: column;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            `;
            
            // Top bar with close button and counter
            const topBar = document.createElement('div');
            topBar.style.cssText = `
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 20px 30px;
            `;
            
            const photoCounter = document.createElement('div');
            photoCounter.style.cssText = `
                color: white;
                font-size: 16px;
                font-weight: 500;
            `;
            photoCounter.textContent = `${currentIndex + 1} / ${imagePaths.length}`;
            
            const closeBtn = document.createElement('button');
            closeBtn.innerHTML = '×';
            closeBtn.style.cssText = `
                background: none;
                border: none;
                color: white;
                font-size: 32px;
                cursor: pointer;
                padding: 5px 10px;
                border-radius: 4px;
                transition: background-color 0.2s;
            `;
            closeBtn.onmouseover = () => closeBtn.style.backgroundColor = 'rgba(255,255,255,0.1)';
            closeBtn.onmouseout = () => closeBtn.style.backgroundColor = 'transparent';
            
            topBar.appendChild(photoCounter);
            topBar.appendChild(closeBtn);
            
            // Main image area
            const imageArea = document.createElement('div');
            imageArea.style.cssText = `
                flex: 1;
                display: flex;
                align-items: center;
                justify-content: center;
                position: relative;
                padding: 20px;
            `;
            
            const img = document.createElement('img');
            img.style.cssText = `
                max-width: calc(100vw - 200px);
                max-height: calc(100vh - 200px);
                width: auto;
                height: auto;
                object-fit: contain;
                border-radius: 8px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.5);
                transition: opacity 0.3s ease;
            `;
            
            // Navigation buttons
            const prevBtn = document.createElement('button');
            prevBtn.innerHTML = '‹';
            prevBtn.style.cssText = `
                position: absolute;
                left: 20px;
                top: 50%;
                transform: translateY(-50%);
                background: rgba(255,255,255,0.2);
                border: none;
                color: white;
                font-size: 40px;
                width: 60px;
                height: 60px;
                border-radius: 50%;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.2s;
                backdrop-filter: blur(10px);
            `;
            prevBtn.onmouseover = () => {
                prevBtn.style.backgroundColor = 'rgba(255,255,255,0.3)';
                prevBtn.style.transform = 'translateY(-50%) scale(1.1)';
            };
            prevBtn.onmouseout = () => {
                prevBtn.style.backgroundColor = 'rgba(255,255,255,0.2)';
                prevBtn.style.transform = 'translateY(-50%) scale(1)';
            };
            
            const nextBtn = document.createElement('button');
            nextBtn.innerHTML = '›';
            nextBtn.style.cssText = prevBtn.style.cssText;
            nextBtn.style.left = 'auto';
            nextBtn.style.right = '20px';
            nextBtn.onmouseover = () => {
                nextBtn.style.backgroundColor = 'rgba(255,255,255,0.3)';
                nextBtn.style.transform = 'translateY(-50%) scale(1.1)';
            };
            nextBtn.onmouseout = () => {
                nextBtn.style.backgroundColor = 'rgba(255,255,255,0.2)';
                nextBtn.style.transform = 'translateY(-50%) scale(1)';
            };
            
            // Update image and counter
            const updateImage = () => {
                img.style.opacity = '0';
                setTimeout(() => {
                    img.src = `/storage/${imagePaths[currentIndex]}`;
                    photoCounter.textContent = `${currentIndex + 1} / ${imagePaths.length}`;
                    
                    // Update button states
                    prevBtn.style.opacity = currentIndex === 0 ? '0.5' : '1';
                    nextBtn.style.opacity = currentIndex === imagePaths.length - 1 ? '0.5' : '1';
                    prevBtn.disabled = currentIndex === 0;
                    nextBtn.disabled = currentIndex === imagePaths.length - 1;
                    
                    img.style.opacity = '1';
                }, 150);
            };
            
            // Navigation functions
            const showPrev = () => {
                if (currentIndex > 0) {
                    currentIndex--;
                    updateImage();
                }
            };
            
            const showNext = () => {
                if (currentIndex < imagePaths.length - 1) {
                    currentIndex++;
                    updateImage();
                }
            };
            
            // Event listeners
            prevBtn.onclick = showPrev;
            nextBtn.onclick = showNext;
            
            // Keyboard navigation
            const handleKeydown = (e) => {
                switch(e.key) {
                    case 'Escape':
                        closeModal();
                        break;
                    case 'ArrowLeft':
                        showPrev();
                        break;
                    case 'ArrowRight':
                        showNext();
                        break;
                }
            };
            
            // Close modal function
            const closeModal = () => {
                document.removeEventListener('keydown', handleKeydown);
                document.body.removeChild(modal);
                document.body.style.overflow = '';
            };
            
            // Assemble modal
            imageArea.appendChild(img);
            imageArea.appendChild(prevBtn);
            imageArea.appendChild(nextBtn);
            
            modal.appendChild(topBar);
            modal.appendChild(imageArea);
            
            // Add to DOM
            document.body.appendChild(modal);
            document.body.style.overflow = 'hidden';
            
            // Initial setup
            updateImage();
            
            // Event listeners
            closeBtn.onclick = closeModal;
            modal.onclick = (e) => {
                if (e.target === modal) closeModal();
            };
            document.addEventListener('keydown', handleKeydown);
            
            // Touch/swipe support for mobile
            let startX = 0;
            let startY = 0;
            
            modal.addEventListener('touchstart', (e) => {
                startX = e.touches[0].clientX;
                startY = e.touches[0].clientY;
            });
            
            modal.addEventListener('touchend', (e) => {
                const endX = e.changedTouches[0].clientX;
                const endY = e.changedTouches[0].clientY;
                const diffX = startX - endX;
                const diffY = startY - endY;
                
                // Only handle horizontal swipes
                if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 50) {
                    if (diffX > 0) {
                        showNext();
                    } else {
                        showPrev();
                    }
                }
            });
        }

        function initializeFallbackLightbox() {
            console.log('Initializing fallback lightbox...');
            document.querySelectorAll('.post-image-trigger').forEach(trigger => {
                trigger.addEventListener('click', function(e) {
                    e.preventDefault();
                    const postId = this.dataset.postId;
                    const imageIndex = parseInt(this.dataset.imageIndex, 10);
                    showFallbackLightbox(postId, imageIndex);
                });
            });
        }

        // Make openLightbox globally available
        window.openLightbox = openLightbox;
    </script>
    
    <!-- GLightbox JS -->
    <script src="https://cdn.jsdelivr.net/npm/glightbox@3/dist/js/glightbox.min.js"></script>
    <script>
        // Initialize GLightbox when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof GLightbox !== 'undefined') {
                window.lightbox = GLightbox({
                    selector: '.post-images-gallery a',
                    touchNavigation: true,
                    loop: true,
                    moreText: 'See all images',
                    autoplayVideos: false
                });
                console.log('GLightbox initialized successfully');
            } else {
                console.error('GLightbox not available');
                initializeFallbackLightbox();
            }
        });
    </script>
    
    @stack('scripts')
    
    <!-- Back to Top Button -->
    <div id="back-to-top" class="fixed bottom-8 right-8 z-50 opacity-0 pointer-events-none transition-all duration-300 ease-in-out transform">
        <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" 
                class="bg-white text-slate-700 p-2 rounded-full shadow-lg hover:bg-slate-50 transition-colors duration-200 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
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
