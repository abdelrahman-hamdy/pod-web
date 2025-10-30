<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>People Of Data - Egypt's Premier AI & Data Community</title>
    <script src="https://cdn.tailwindcss.com/3.4.16">
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: "#4f46e5",
                        secondary: "#64748b",
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
    <style>
        :where([class^="ri-"])::before {
            content: "\f3c2";
        }

        .typewriter {
            overflow: hidden;
            border-right: 0.15em solid #4f46e5;
            white-space: nowrap;
            margin: 0 auto;
            letter-spacing: 0.15em;
            animation: typing 3.5s steps(40, end), blink-caret 0.75s step-end infinite;
        }

        @keyframes typing {
            from {
                width: 0
            }

            to {
                width: 100%
            }
        }

        @keyframes blink-caret {

            from,
            to {
                border-color: transparent
            }

            50% {
                border-color: #4f46e5
            }
        }



        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: #4f46e5;
            border-radius: 50%;
            animation: particle-float 8s infinite ease-in-out;
        }

        @keyframes particle-float {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg)
            }

            33% {
                transform: translateY(-30px) rotate(120deg)
            }

            66% {
                transform: translateY(-15px) rotate(240deg)
            }
        }

        /* Hero Section Scroll Animations */
        .hero-animate {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .hero-animate.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Sequential delays for hero elements */
        .hero-animate-delay-1 {
            transition-delay: 0.1s;
        }

        .hero-animate-delay-2 {
            transition-delay: 0.3s;
        }

        .hero-animate-delay-3 {
            transition-delay: 0.5s;
        }

        .hero-animate-delay-4 {
            transition-delay: 0.7s;
        }

        .hero-animate-delay-5 {
            transition-delay: 0.9s;
        }

        .hero-animate-delay-6 {
            transition-delay: 1.1s;
        }

        .hero-animate-delay-7 {
            transition-delay: 1.3s;
        }

        .hero-animate-delay-8 {
            transition-delay: 1.5s;
        }

        .hero-animate-delay-9 {
            transition-delay: 1.7s;
        }

        .hero-animate-delay-10 {
            transition-delay: 2.0s;
        }

        /* Custom background animation - opacity and translate */
        .hero-bg-animate {
            opacity: 0;
            transform: translateY(50px);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .hero-bg-animate.visible {
            opacity: 0.2;
            transform: translateY(0);
        }

        /* Second Section Scroll Animations */
        .section2-animate {
            opacity: 0;
            transform: translateX(-40px);
            transition: all 0.7s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            will-change: transform, opacity;
        }

        .section2-animate.visible {
            opacity: 1;
            transform: translateX(0);
        }

        .section2-circle-animate {
            opacity: 0;
            transform: scale(0.85);
            transition: all 0.9s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            will-change: transform, opacity;
        }

        .section2-circle-animate.visible {
            opacity: 1;
            transform: scale(1);
        }

        /* Sequential delays for section 2 elements - smoother overlapping */
        .section2-delay-1 {
            transition-delay: 0.1s;
        }

        .section2-delay-2 {
            transition-delay: 0.15s;
        }

        .section2-delay-3 {
            transition-delay: 0.2s;
        }

        .section2-delay-4 {
            transition-delay: 0.25s;
        }

        .section2-delay-5 {
            transition-delay: 0.4s;
        }

        /* Override transition delays for hover states */
        .section2-animate:hover {
            transition-delay: 0s !important;
        }

        .section2-animate:hover * {
            transition-delay: 0s !important;
        }

        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease-out;
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .counter {
            font-variant-numeric: tabular-nums;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        }



        .feature-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .feature-card:hover {
            transform: translateY(-12px) scale(1.02);
        }

        .feature-icon {
            transition: all 0.3s ease;
        }

        .feature-card:hover .feature-icon {
            transform: rotate(10deg) scale(1.1);
        }



        .morphing-bg {
            background: linear-gradient(45deg, #4f46e5, #7c3aed, #3b82f6, #8b5cf6);
            background-size: 400% 400%;
            animation: morphing 8s ease-in-out infinite;
        }

        @keyframes morphing {

            0%,
            100% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }
        }



        /* Infinite Gradient Animation for Join Community Button */
        .animate-gradient-x {
            background-size: 200% 200%;
            animation: gradient-x 3s ease infinite;
        }

        @keyframes gradient-x {

            0%,
            100% {
                background-position: 0% 50%;
            }

            25% {
                background-position: 100% 50%;
            }

            50% {
                background-position: 100% 100%;
            }

            75% {
                background-position: 0% 100%;
            }
        }

        .join-community-btn {
            background: linear-gradient(-45deg, #4f46e5, #7c3aed, #3b82f6, #8b5cf6, #4f46e5);
            background-size: 400% 400%;
            animation: gradient-shift 4s ease infinite;
        }

        @keyframes gradient-shift {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .join-community-btn:hover {
            background: linear-gradient(-45deg, #312e81, #5b21b6, #3730a3, #312e81, #1e1b4b);
            background-size: 400% 400%;
            animation: gradient-shift 2s ease infinite;
        }

        /* Enhanced circle transitions for the JavaScript-based hover system */
        #network-image,
        #knowledge-image,
        #career-image,
        #network-labels,
        #knowledge-labels,
        #career-labels,
        #circle-content {
            transition: opacity 0.5s ease-in-out;
        }

        #knowledge-labels div,
        #career-labels div,
        #network-labels div {
            width: 130px;
            text-align: center;
            margin: 0;
        }

        /* Network Labels Animation - Fade in with translate from right to left */
        #network-labels {
            opacity: 0;
            transform: translateX(50px);
            transition: opacity 0.5s ease-in-out, transform 0.5s ease-in-out;
        }

        #network-labels.show {
            opacity: 1;
            transform: translateX(0);
        }

        /* Knowledge Labels Animation - Fade in with translate from left to right */
        #knowledge-labels {
            opacity: 0;
            transform: translateX(-50px);
            transition: opacity 0.5s ease-in-out, transform 0.5s ease-in-out;
        }

        #knowledge-labels.show {
            opacity: 1;
            transform: translateX(0);
        }

        /* Career Labels Animation - Rotate with fade in */
        #career-labels {
            opacity: 0;
            transform: rotate(-15deg) scale(0.8);
            transition: opacity 0.5s ease-in-out, transform 0.5s ease-in-out;
        }

        #career-labels.show {
            opacity: 1;
            transform: rotate(0deg) scale(1);
        }



        /* Feature Card Redesign */
        .glass-feature-card {
            background: rgba(255, 255, 255, 0.18);
            border-radius: 2rem;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.10);
            backdrop-filter: blur(12px);
            border: 1.5px solid rgba(255, 255, 255, 0.25);
            padding: 2.5rem 2rem 2rem 2rem;
            position: relative;
            overflow: hidden;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s;
            min-height: 340px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
        }

        .glass-feature-card:hover {
            transform: translateY(-12px) scale(1.04) rotate(-1deg);
            box-shadow: 0 24px 48px 0 rgba(79, 70, 229, 0.18), 0 1.5px 8px 0 rgba(0, 0, 0, 0.04);
        }

        .feature-icon-animated {
            width: 72px;
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 1.5rem;
            margin-bottom: 2rem;
            font-size: 2.5rem;
            color: #fff;
            box-shadow: 0 4px 24px 0 rgba(79, 70, 229, 0.18);
            position: relative;
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.4s;
            z-index: 1;
            animation: feature-icon-float 4s ease-in-out infinite;
        }

        .glass-feature-card:hover .feature-icon-animated {
            transform: scale(1.12) rotate(8deg);
            box-shadow: 0 8px 32px 0 rgba(79, 70, 229, 0.25);
            animation-play-state: paused;
        }

        @keyframes feature-icon-float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .feature-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1rem;
            text-align: center;
        }

        .feature-desc {
            color: #64748b;
            font-size: 1.08rem;
            text-align: center;
            line-height: 1.7;
            margin-bottom: 0;
        }

        /* Event Card Redesign */
        .event-card {
            background: rgba(255, 255, 255, 0.18);
            border-radius: 2rem;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.10);
            backdrop-filter: blur(10px);
            border: 1.5px solid rgba(255, 255, 255, 0.25);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            /* Remove translate/scale on hover */
            transition: box-shadow 0.3s;
            min-height: 480px;
            position: relative;
        }

        .event-card:hover {
            /* Only shadow, no transform */
            box-shadow: 0 24px 48px 0 rgba(139, 92, 246, 0.18), 0 1.5px 8px 0 rgba(124, 58, 237, 0.10);
        }

        .event-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            /* Remove zoom on hover */
            transition: none;
        }

        /* Discover Events Button Arrow Animation */
        @keyframes arrow-bounce-x {

            0%,
            100% {
                transform: translateX(0);
            }

            50% {
                transform: translateX(12px);
            }
        }

        .discover-events-btn .ri-arrow-right-line {
            animation: arrow-bounce-x 2.2s ease-in-out infinite;
            display: inline-block;
        }

        .event-type-badge-simple {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(124, 58, 237, 0.10);
            color: #4f46e5;
            border-radius: 1rem;
            padding: 0.4rem 1.1rem;
            font-size: 0.95rem;
            font-weight: 600;
            box-shadow: none;
            z-index: 2;
        }

        .discover-events-btn {
            display: inline-flex;
            align-items: center;
            background: none;
            color: #4f46e5;
            font-weight: 700;
            font-size: 1.15rem;
            border: none;
            padding: 0.7rem 1.5rem;
            border-radius: 2rem;
            transition: background 0.2s, color 0.2s;
            cursor: pointer;
        }

        .discover-events-btn:hover {
            background: rgba(124, 58, 237, 0.08);
            color: #312e81;
        }

        .event-img-container {
            position: relative;
            width: 100%;
            height: 210px;
            overflow: hidden;
        }

        .event-date-badge {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background: rgba(255, 255, 255, 0.85);
            color: #4f46e5;
            border-radius: 1rem;
            padding: 0.5rem 1rem;
            font-weight: 700;
            font-size: 1rem;
            box-shadow: 0 2px 8px 0 rgba(79, 70, 229, 0.08);
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 2;
        }

        .event-date-day {
            font-size: 1.5rem;
            font-weight: 900;
            line-height: 1;
        }

        .event-date-year {
            font-size: 0.85rem;
            font-weight: 500;
            color: #64748b;
        }

        .event-content {
            padding: 2rem 1.5rem 1.5rem 1.5rem;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .event-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.7rem;
        }

        .event-desc {
            color: #64748b;
            font-size: 1.05rem;
            margin-bottom: 1.5rem;
            line-height: 1.6;
            flex: 1;
        }

        .event-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .event-attendance {
            display: flex;
            align-items: center;
            color: #4f46e5;
            font-weight: 600;
            font-size: 1rem;
            gap: 0.5rem;
        }

        .event-join-btn {
            background: linear-gradient(90deg, #4f46e5 0%, #7c3aed 100%);
            color: #fff;
            border: none;
            border-radius: 1rem;
            padding: 0.7rem 1.6rem;
            font-size: 1rem;
            font-weight: 700;
            box-shadow: 0 2px 8px 0 rgba(79, 70, 229, 0.10);
            transition: background 0.3s, transform 0.2s;
            cursor: pointer;
        }

        .event-join-btn:hover {
            background: linear-gradient(90deg, #312e81 0%, #5b21b6 100%);
            transform: scale(1.06);
        }

        /* Animated Events Section Background - Bigger, Softer, More Colorful Spots */
        .animated-spot {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            filter: blur(64px);
            opacity: 0.18;
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

            0%,
            100% {
                transform: translateY(0) scale(1);
                opacity: 0.28;
            }

            40% {
                transform: translateY(60px) scale(1.12);
                opacity: 0.38;
            }

            70% {
                transform: translateY(-40px) scale(0.95);
                opacity: 0.18;
            }
        }

        @keyframes spot2move {

            0%,
            100% {
                transform: translateY(0) scale(1);
                opacity: 0.28;
            }

            30% {
                transform: translateY(-70px) scale(1.13);
                opacity: 0.18;
            }

            60% {
                transform: translateY(50px) scale(0.92);
                opacity: 0.32;
            }
        }

        @keyframes spot3move {

            0%,
            100% {
                transform: translateX(0) scale(1);
                opacity: 0.28;
            }

            50% {
                transform: translateX(-80px) scale(1.09);
                opacity: 0.18;
            }

            80% {
                transform: translateX(60px) scale(0.93);
                opacity: 0.32;
            }
        }

        @keyframes spot4move {

            0%,
            100% {
                transform: translateX(0) scale(1);
                opacity: 0.28;
            }

            40% {
                transform: translateX(60px) scale(1.08);
                opacity: 0.18;
            }

            70% {
                transform: translateX(-40px) scale(0.95);
                opacity: 0.32;
            }
        }

        /* Footer POD logo tweaks */
        .footer-pod-logo {
            background: none !important;
            border-radius: 16px !important;
            border: 1.5px solid rgba(100, 116, 139, 0.18);
            filter: invert(1);
            width: 2.75rem;
            height: 2.75rem;
            object-fit: contain;
            padding: 0.35rem;
        }

        /* Newsletter form compound pill design */
        .newsletter-form {
            display: flex;
            width: 100%;
            border-radius: 9999px;
            overflow: hidden;
            box-shadow: 0 2px 8px 0 rgba(100, 116, 139, 0.06);
            border: 1.5px solid rgba(100, 116, 139, 0.13);
            background: rgba(30, 41, 59, 0.85);
        }

        .newsletter-form input[type="email"] {
            flex: 1;
            border: none;
            outline: none;
            background: transparent;
            color: #fff;
            padding: 0.85rem 1.2rem;
            font-size: 1rem;
            border-radius: 9999px 0 0 9999px;
            min-width: 100px
        }

        .newsletter-form input[type="email"]::placeholder {
            color: #cbd5e1;
        }

        .newsletter-form button {
            background: linear-gradient(90deg, #4f46e5 0%, #7c3aed 100%);
            color: #fff;
            border: none;
            padding: 0 1.5rem;
            font-size: 0.9rem;
            min-width: 100px;
            font-weight: 600;
            border-radius: 0 9999px 9999px 0;
            transition: background 0.2s;
            cursor: pointer;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .newsletter-form button:hover {
            background: linear-gradient(90deg, #312e81 0%, #5b21b6 100%);
            transform: scale(1.06);
        }

        /* Hero Counters Box Soft Shadow with Shine Effect */
        .counters-box {
            position: relative;
            overflow: hidden;
            animation: soft-float 4s ease-in-out infinite;
        }

        /* Soft animating shadow */
        .counters-box::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 120%;
            height: 120%;
            background: radial-gradient(ellipse, rgba(79, 70, 229, 0.15) 0%, rgba(124, 58, 237, 0.1) 30%, transparent 70%);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            z-index: -1;
            animation: shadow-pulse 6s ease-in-out infinite;
        }

        /* Glass reflection shine effect - Bigger and More Attractive */
        .counters-box::after {
            content: '';
            position: absolute;
            top: -20%;
            left: -200%;
            width: 150%;
            height: 140%;
            background: linear-gradient(90deg,
                    transparent 0%,
                    transparent 15%,
                    rgba(255, 255, 255, 0.05) 25%,
                    rgba(255, 255, 255, 0.2) 35%,
                    rgba(255, 255, 255, 0.6) 45%,
                    rgba(255, 255, 255, 0.95) 50%,
                    rgba(255, 255, 255, 0.6) 55%,
                    rgba(255, 255, 255, 0.2) 65%,
                    rgba(255, 255, 255, 0.05) 75%,
                    transparent 85%,
                    transparent 100%);
            transform: skewX(-30deg) scaleY(1.2);
            animation: glass-shine 5s ease-in-out infinite;
            z-index: 1;
            pointer-events: none;
            filter: blur(0.5px);
        }

        @keyframes soft-float {

            0%,
            100% {
                transform: translateY(0);
                box-shadow: 0 20px 40px -12px rgba(79, 70, 229, 0.15);
            }

            50% {
                transform: translateY(-8px);
                box-shadow: 0 30px 60px -12px rgba(79, 70, 229, 0.25);
            }
        }

        @keyframes shadow-pulse {

            0%,
            100% {
                transform: translate(-50%, -50%) scale(1);
                opacity: 0.6;
            }

            50% {
                transform: translate(-50%, -50%) scale(1.2);
                opacity: 0.9;
            }
        }

        @keyframes glass-shine {
            0% {
                left: -200%;
                opacity: 0;
                transform: skewX(-30deg) scaleY(1.2) scaleX(0.8);
            }

            15% {
                opacity: 1;
                transform: skewX(-30deg) scaleY(1.2) scaleX(1);
            }

            85% {
                opacity: 1;
                transform: skewX(-30deg) scaleY(1.2) scaleX(1);
            }

            100% {
                left: 200%;
                opacity: 0;
                transform: skewX(-30deg) scaleY(1.2) scaleX(0.8);
            }
        }

        .counters-box>* {
            position: relative;
            z-index: 2;
        }

        /* Mobile menu styles */
        body.menu-open {
            overflow: hidden;
        }

        #mobile-menu-overlay {
            z-index: 9999;
        }

        #mobile-menu {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>
</head>

<body class="bg-white text-slate-800">
    <nav class="fixed w-full top-0 z-50 px-4 py-3">
        <div class="max-w-7xl mx-auto">
            <div class="bg-white/60 backdrop-blur-sm border border-slate-200/50 rounded-full px-6 py-4 transition-all duration-300" id="navbar">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 flex items-center justify-center">
                            <img src="{{ asset('storage/assets/pod-logo.png') }}" alt="Logo" class="w-full h-full object-contain">
                        </div>
                        <span class="text-2xl font-extrabold text-slate-900 tracking-tight">People Of Data</span>
                    </div>
                    <div class="hidden md:flex items-center space-x-8">
                        <a href="#story" class="text-slate-600 font-semibold hover:text-primary transition-colors">Story</a>
                        <a href="#features" class="text-slate-600 font-semibold hover:text-primary transition-colors">Features</a>
                        <a href="#events" class="text-slate-600 font-semibold hover:text-primary transition-colors">Events</a>
                        <a href="#community" class="text-slate-600 font-semibold hover:text-primary transition-colors">Community</a>
                        @auth
                            <a href="{{ route('home') }}" class="flex items-center space-x-2 text-slate-600 font-semibold hover:text-primary transition-colors border border-slate-200 rounded-full pl-1 pr-4 py-1 hover:border-primary/50">
                                <img class="w-8 h-8 rounded-full object-cover" src="{{ Auth::user()->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&color=7F9CF5&background=EBF4FF' }}" alt="{{ Auth::user()->name }}">
                                <span>{{ Auth::user()->name }}</span>
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                                class="bg-gradient-to-r from-primary via-indigo-600 to-purple-600 text-white px-6 py-2 rounded-full hover:bg-indigo-600 transition-colors whitespace-nowrap font-semibold animate-gradient-x">
                                Join Now
                            </a>
                        @endauth
                    </div>
                    <button id="mobile-menu-btn"
                        class="md:hidden w-8 h-8 flex items-center justify-center text-slate-600 hover:text-primary transition-colors">
                        <i class="ri-menu-line text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu Overlay -->
    <div id="mobile-menu-overlay" class="fixed inset-0 z-40 hidden">
        <div id="mobile-menu"
            class="absolute top-20 left-4 right-4 bg-white/60 backdrop-blur-sm border border-slate-200/50 rounded-2xl transform translate-y-[120px] transition-transform duration-300 ease-out">
            <div class="max-w-7xl mx-auto px-4 py-6">
                <div class="flex flex-col space-y-6">
                    <a href="#story" class="text-slate-600 font-semibold hover:text-primary transition-colors text-lg py-2">Story</a>
                    <a href="#features" class="text-slate-600 font-semibold hover:text-primary transition-colors text-lg py-2">Features</a>
                    <a href="#events" class="text-slate-600 font-semibold hover:text-primary transition-colors text-lg py-2">Events</a>
                    <a href="#community" class="text-slate-600 font-semibold hover:text-primary transition-colors text-lg py-2">Community</a>
                    <div class="pt-4">
                        @auth
                            <a href="{{ route('home') }}" class="inline-flex items-center space-x-2 text-slate-600 font-semibold hover:text-primary transition-colors text-lg border border-slate-200 rounded-full pl-1 pr-4 py-1 hover:border-primary/50">
                                <img class="w-8 h-8 rounded-full object-cover" src="{{ Auth::user()->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&color=7F9CF5&background=EBF4FF' }}" alt="{{ Auth::user()->name }}">
                                <span>{{ Auth::user()->name }}</span>
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                                class="w-full text-center block bg-gradient-to-r from-primary via-indigo-600 to-purple-600 text-white px-6 py-3 rounded-full hover:bg-indigo-600 transition-colors font-semibold animate-gradient-x">
                                Join Now
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
    </nav>
    <section class="relative min-h-screen flex items-center justify-center overflow-hidden pb-20">
        <!-- Hero Background Image -->
        <div class="hero-bg-animate hero-animate-delay-10 absolute inset-0"
            style="background-image: url('{{ asset('storage/assets/images/hero section background3.png') }}'); background-position: bottom; background-size: 70rem; background-repeat: repeat-x;">
        </div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_50%_0%,rgba(79,70,229,0.15),transparent_50%)]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_80%_40%,rgba(124,58,237,0.12),transparent_50%)]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_60%,rgba(79,70,229,0.08),transparent_50%)]"></div>
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full pt-32 flex flex-col items-center">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h1
                    class="hero-animate hero-animate-delay-1 text-5xl lg:text-6xl font-black mb-8 text-transparent bg-clip-text bg-gradient-to-r from-slate-900 to-slate-600">
                    Transform Your Data Journey
                </h1>
                <p class="hero-animate hero-animate-delay-2 text-2xl text-slate-700 mb-10 font-medium leading-relaxed">
                    Join Egypt's most innovative community of data scientists, AI engineers, and tech enthusiasts.
                </p>
                <div class="hero-animate hero-animate-delay-3 flex flex-wrap justify-center gap-6">
                    <a href="{{ route('register') }}"
                        class="bg-primary text-white px-12 py-5 !rounded-button text-lg font-semibold hover:shadow-xl hover:shadow-primary/20 transition-all whitespace-nowrap">
                        Get Started Free
                    </a>
                    <button
                        class="bg-white text-slate-800 px-12 py-5 !rounded-button text-lg font-semibold shadow-2xl shadow-slate-200/40 hover:shadow-xl hover:shadow-slate-200/60 transition-all whitespace-nowrap">
                        Watch Demo
                    </button>
                </div>
            </div>
            <div class="hero-animate hero-animate-delay-4 relative w-full max-w-3xl mx-auto">
                <img src="https://i.ibb.co/3yDYZc8v/pod-cover-image.png" alt="AI Community Collaboration"
                    class="w-full rounded-t-2xl object-cover shadow-2xl shadow-slate-200/40">
                <div class="counters-box-wrapper relative">
                    <div
                        class="hero-animate-delay-5 absolute -top-6 left-1/2 transform -translate-x-1/2 bg-primary text-white px-8 py-3 rounded-full font-medium shadow-lg shadow-primary/30 z-10 text-center whitespace-nowrap">
                        Real-time Community Stats
                    </div>
                    <div
                        class="hero-animate hero-animate-delay-6 counters-box relative bg-white rounded-3xl p-8 -mt-20 mx-8 shadow-2xl shadow-slate-200/40 backdrop-blur-sm border border-slate-100">

                        <div class="grid grid-cols-3 gap-12">
                            <div class="hero-animate hero-animate-delay-7 text-center relative group">
                                <div
                                    class="absolute inset-0 bg-gradient-to-b from-primary/5 to-transparent rounded-2xl transition-opacity opacity-0 group-hover:opacity-100">
                                </div>
                                <div class="relative">
                                    <div class="flex items-center justify-center mb-3">
                                        <i class="ri-team-line text-primary text-3xl"></i>
                                    </div>
                                    <span class="counter text-4xl font-bold text-slate-800 mb-2 block" data-target="3847">0</span>
                                    <p class="text-slate-600 font-medium">Active Members</p>
                                </div>
                            </div>
                            <div class="hero-animate hero-animate-delay-8 text-center relative group">
                                <div
                                    class="absolute inset-0 bg-gradient-to-b from-primary/5 to-transparent rounded-2xl transition-opacity opacity-0 group-hover:opacity-100">
                                </div>
                                <div class="relative">
                                    <div class="flex items-center justify-center mb-3">
                                        <i class="ri-briefcase-line text-primary text-3xl"></i>
                                    </div>
                                    <span class="counter text-4xl font-bold text-slate-800 mb-2 block" data-target="256">0</span>
                                    <p class="text-slate-600 font-medium">Job Opportunities</p>
                                </div>
                            </div>
                            <div class="hero-animate hero-animate-delay-9 text-center relative group">
                                <div
                                    class="absolute inset-0 bg-gradient-to-b from-primary/5 to-transparent rounded-2xl transition-opacity opacity-0 group-hover:opacity-100">
                                </div>
                                <div class="relative">
                                    <div class="flex items-center justify-center mb-3">
                                        <i class="ri-discuss-line text-primary text-3xl"></i>
                                    </div>
                                    <span class="counter text-4xl font-bold text-slate-800 mb-2 block" data-target="2138">0</span>
                                    <p class="text-slate-600 font-medium">Community Posts</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
            <button onclick="scrollToNextSection()"
                class="flex items-center gap-2 text-slate-500 animate-bounce hover:text-primary transition-colors cursor-pointer group">
                <span class="text-sm font-medium group-hover:scale-105 transition-transform">Scroll to explore</span>
                <i class="ri-arrow-down-line transition-transform"></i>
            </button>
        </div>
    </section>
    <section id="story" class="py-16 sm:py-20 md:py-24 lg:py-32 relative overflow-hidden">
        <canvas id="particleCanvas" class="absolute inset-0 w-full h-full pointer-events-none z-0"></canvas>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center mb-12 sm:mb-16 md:mb-20 section2-animate section2-delay-1">
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold text-slate-900 mb-4 sm:mb-6">Our Logo Tells Our Story</h2>
                <p class="text-lg sm:text-xl text-secondary max-w-3xl mx-auto px-4"><span
                        class="font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-700 to-purple-700">Three pillars of growth</span>
                    connected by one <span class="font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-700 to-purple-700">powerful
                        community</span>.</p>
            </div>

            <!-- POD Logo Structure - Refactored with Tailwind Advanced Selectors -->
            <div class="flex justify-center items-center mb-12 sm:mb-16 px-4">
                <div class="flex items-center gap-4 sm:gap-8 md:gap-12 lg:gap-24">
                    <!-- Three Horizontal Lines (Networking, Knowledge, Career) -->
                    <div class="flex flex-col gap-4 sm:gap-8 md:gap-12 lg:gap-20">
                        <!-- Networking Line -->
                        <div class="group cursor-pointer section2-animate section2-delay-2 hover:scale-105 transition-transform duration-300"
                            data-target="network">
                            <div
                                class="flex items-center gap-2 sm:gap-4 md:gap-6 p-2 sm:p-3 md:p-4 lg:p-5 bg-slate-900 rounded-[8px] sm:rounded-[10px] rounded-br-[20px] sm:rounded-br-[25px] group-hover:bg-slate-800 group-hover:shadow-2xl group-hover:shadow-indigo-500/40 transition-all duration-300">
                                <i
                                    class="ri-team-line text-lg sm:text-xl md:text-2xl relative z-10 group-hover:scale-125 transition-transform duration-300 bg-gradient-to-r from-indigo-500 to-purple-600 bg-clip-text text-transparent"></i>
                                <div>
                                    <h3 class="text-sm sm:text-lg md:text-xl font-bold text-white mb-1">Networking</h3>
                                    <p class="text-xs sm:text-sm md:text-base text-slate-300">Connect with 2,800+ professionals</p>
                                </div>
                            </div>
                        </div>

                        <!-- Knowledge Line -->
                        <div class="group cursor-pointer section2-animate section2-delay-3 hover:scale-105 transition-transform duration-300 pr-2 sm:pr-4 md:pr-6 lg:pr-10"
                            data-target="knowledge">
                            <div
                                class="flex items-center gap-2 sm:gap-4 md:gap-6 p-2 sm:p-3 md:p-4 lg:p-5 bg-slate-900 rounded-[8px] sm:rounded-[10px] group-hover:bg-slate-800 group-hover:shadow-2xl group-hover:shadow-indigo-500/40 transition-all duration-300">
                                <i
                                    class="ri-lightbulb-line text-lg sm:text-xl md:text-2xl relative z-10 group-hover:scale-125 transition-transform duration-300 bg-gradient-to-r from-indigo-500 to-purple-600 bg-clip-text text-transparent"></i>
                                <div>
                                    <h3 class="text-sm sm:text-lg md:text-xl font-bold text-white mb-1">Knowledge</h3>
                                    <p class="text-xs sm:text-sm md:text-base text-slate-300">Access exclusive workshops & expertise</p>
                                </div>
                            </div>
                        </div>

                        <!-- Career Line -->
                        <div class="group cursor-pointer section2-animate section2-delay-4 hover:scale-105 transition-transform duration-300"
                            data-target="career">
                            <div
                                class="flex items-center gap-2 sm:gap-4 md:gap-6 p-2 sm:p-3 md:p-4 lg:p-5 bg-slate-900 rounded-[8px] sm:rounded-[10px] rounded-tr-[20px] sm:rounded-tr-[25px] group-hover:bg-slate-800 group-hover:shadow-2xl group-hover:shadow-indigo-500/40 transition-all duration-300">
                                <i
                                    class="ri-rocket-line text-lg sm:text-xl md:text-2xl relative z-10 group-hover:scale-125 transition-transform duration-300 bg-gradient-to-r from-indigo-500 to-purple-600 bg-clip-text text-transparent"></i>
                                <div>
                                    <h3 class="text-sm sm:text-lg md:text-xl font-bold text-white mb-1">Career</h3>
                                    <p class="text-xs sm:text-sm md:text-base text-slate-300">Priority access to job opportunities</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Circle (Community) - Refactored with Simple CSS -->
                    <div class="relative section2-circle-animate section2-delay-5 overflow-visible">
                        <div id="community-circle"
                            class="w-[200px] h-[196px] sm:w-[280px] sm:h-[275px] md:w-[350px] md:h-[343px] lg:w-[400px] lg:h-[392px] xl:w-[500px] xl:h-[490px] border-[30px] sm:border-[40px] md:border-[50px] lg:border-[60px] xl:border-[70px] border-slate-900 rounded-full flex items-center justify-center relative overflow-hidden shadow-2xl shadow-slate-900/30 transition-all duration-500 hover:scale-105 overflow-visible">

                            <!-- Default background -->
                            <div class="absolute inset-0 bg-gradient-to-r from-primary/5 to-indigo-500/5 rounded-full animate-pulse"></div>

                            <!-- Networking Image -->
                            <div id="network-image" class="absolute inset-0 opacity-0 transition-all duration-500 ease-in-out">
                                <img src="{{ asset('storage/assets/network-growth.png') }}" alt="Networking" class="w-full h-full object-cover rounded-full">
                            </div>

                            <!-- Knowledge Image -->
                            <div id="knowledge-image" class="absolute inset-0 opacity-0 transition-all duration-500 ease-in-out">
                                <img src="{{ asset('storage/assets/knowledge-exchange.png') }}" alt="Knowledge" class="w-full h-full object-cover rounded-full">
                            </div>

                            <!-- Career Image -->
                            <div id="career-image" class="absolute inset-0 opacity-0 transition-all duration-500 ease-in-out">
                                <img src="{{ asset('storage/assets/career-growth.png') }}" alt="Career" class="w-full h-full object-cover rounded-full">
                            </div>

                            <!-- Default Content -->
                            <div id="circle-content" class="relative z-10 text-center transition-all duration-500 ease-in-out opacity-100">
                                <a href="{{ route('register') }}"
                                    class="w-[100px] h-[100px] sm:w-[140px] sm:h-[140px] md:w-[175px] md:h-[175px] lg:w-[200px] lg:h-[200px] xl:w-[250px] xl:h-[250px] join-community-btn text-white rounded-full text-xs sm:text-sm md:text-lg lg:text-xl xl:text-2xl font-bold shadow-2xl shadow-primary/30 hover:shadow-3xl hover:shadow-primary/50 transition-all duration-500 transform hover:scale-110 animate-gradient-x flex items-center justify-center text-center">
                                    Join Our Community
                                </a>
                            </div>

                            <!-- Networking Labels - Positioned outside the circle -->
                            <div id="network-labels"
                                class="absolute inset-0 pointer-events-none opacity-0 transition-all duration-500 ease-in-out hidden lg:block">
                                <div
                                    class="absolute top-1/4 right-[-120px] transform -translate-y-full bg-indigo-500/90 backdrop-blur-sm text-white px-4 py-4 rounded-full text-sm font-medium shadow-lg">
                                    Meetups
                                </div>
                                <div
                                    class="absolute top-1/2 right-[-160px] transform -translate-y-1/2 bg-indigo-500/90 backdrop-blur-sm text-white px-4 py-4 rounded-full text-sm font-medium shadow-lg">
                                    Mentorship
                                </div>
                                <div
                                    class="absolute bottom-1/4 right-[-120px] transform translate-y-full bg-indigo-500/90 backdrop-blur-sm text-white px-4 py-4 rounded-full text-sm font-medium shadow-lg">
                                    Collaboration
                                </div>
                            </div>

                            <!-- Knowledge Labels - Positioned outside the circle -->
                            <div id="knowledge-labels"
                                class="absolute inset-0 pointer-events-none opacity-0 transition-all duration-500 ease-in-out hidden lg:block">
                                <div
                                    class="absolute top-1/4 left-0 transform -translate-x-full -translate-y-full bg-green-500/90 backdrop-blur-sm text-white px-4 py-4 rounded-full text-sm font-medium shadow-lg">
                                    Workshops
                                </div>
                                <div
                                    class="absolute top-1/2 left-[-40px] transform -translate-x-full -translate-y-1/2 bg-green-500/90 backdrop-blur-sm text-white px-4 py-4 rounded-full text-sm font-medium shadow-lg">
                                    Talks
                                </div>
                                <div
                                    class="absolute bottom-1/4 left-0 transform -translate-x-full translate-y-full bg-green-500/90 backdrop-blur-sm text-white px-4 py-4 rounded-full text-sm font-medium shadow-lg">
                                    Resources
                                </div>
                            </div>

                            <!-- Career Labels - Positioned outside the circle -->
                            <div id="career-labels"
                                class="absolute inset-0 pointer-events-none opacity-0 transition-all duration-500 ease-in-out hidden lg:block">
                                <div
                                    class="label absolute top-1/4 right-[-120px] transform -translate-y-full bg-pink-500/90 backdrop-blur-sm text-white px-4 py-4 rounded-full text-sm font-medium shadow-lg">
                                    Jobs
                                </div>
                                <div
                                    class="label absolute top-1/2 right-[-160px] transform -translate-y-1/2 bg-pink-500/90 backdrop-blur-sm text-white px-4 py-4 rounded-full text-sm font-medium shadow-lg">
                                    Internships
                                </div>
                                <div
                                    class="label absolute bottom-1/4 right-[-120px] transform translate-y-full bg-pink-500/90 backdrop-blur-sm text-white px-4 py-4 rounded-full text-sm font-medium shadow-lg">
                                    Hackathons
                                </div>
                            </div>

                        </div>

                        <!-- Enhanced orbiting elements around the circle -->
                        <div class="absolute -top-2 sm:-top-4 -right-2 sm:-right-4 w-3 h-3 sm:w-6 sm:h-6 bg-primary/30 rounded-full animate-ping">
                        </div>
                        <div class="absolute -bottom-2 sm:-bottom-4 -left-2 sm:-left-4 w-2.5 h-2.5 sm:w-5 sm:h-5 bg-indigo-500/30 rounded-full animate-ping"
                            style="animation-delay: 1s;"></div>
                        <div class="absolute top-1/2 -right-3 sm:-right-6 w-1.5 h-1.5 sm:w-3 sm:h-3 bg-primary/40 rounded-full animate-pulse"></div>
                        <div class="absolute top-1/2 -left-3 sm:-left-6 w-1.5 h-1.5 sm:w-3 sm:h-3 bg-indigo-500/40 rounded-full animate-pulse"
                            style="animation-delay: 0.5s;">
                        </div>
                        <div class="absolute -top-1 sm:-top-2 left-1/2 w-2 h-2 sm:w-4 sm:h-4 bg-primary/25 rounded-full animate-bounce"
                            style="animation-delay: 0.3s;"></div>
                        <div class="absolute -bottom-1 sm:-bottom-2 right-1/2 w-2 h-2 sm:w-4 sm:h-4 bg-indigo-500/25 rounded-full animate-bounce"
                            style="animation-delay: 0.7s;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section id="features" class="py-32 relative overflow-hidden bg-gradient-to-br from-white via-slate-50/50 to-indigo-50/30">
        <div class="absolute inset-0">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(79,70,229,0.05),transparent_50%)]"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_80%_80%,rgba(124,58,237,0.05),transparent_50%)]"></div>
            <div class="absolute w-full h-full opacity-30">
                <svg class="absolute w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <defs>
                        <pattern id="grid" width="8" height="8" patternUnits="userSpaceOnUse">
                            <path d="M 8 0 L 0 0 0 8" fill="none" stroke="rgba(79,70,229,0.2)" stroke-width="0.2" />
                        </pattern>
                    </defs>
                    <rect width="100" height="100" fill="url(#grid)" />
                </svg>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center mb-20 fade-in">
                <h2 class="text-5xl font-bold text-slate-900 mb-6">Core Platform Features</h2>
                <p class="text-xl text-secondary max-w-3xl mx-auto">
                    Explore the essential tools and experiences that make People Of Data Egypt's most impactful AI & Data community.
                </p>
            </div>
            <div class="relative">
                <div
                    class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 morphing-bg rounded-full opacity-10 blur-3xl">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10 relative z-10">
                    <!-- Feature 1 -->
                    <div class="feature-card glass-feature-card group cursor-pointer">
                        <div class="feature-icon-animated bg-gradient-to-tr from-blue-600 via-indigo-600 to-purple-600">
                            <i class="ri-verified-badge-line"></i>
                        </div>
                        <h3 class="feature-title">Verified Member Directory</h3>
                        <p class="feature-desc">
                            Discover and connect with Egypt's top AI & data professionals. Every profile is verified for authenticity, expertise, and
                            active contribution.
                        </p>
                    </div>
                    <!-- Feature 2 -->
                    <div class="feature-card glass-feature-card group cursor-pointer">
                        <div class="feature-icon-animated bg-gradient-to-tr from-emerald-500 via-teal-500 to-cyan-500">
                            <i class="ri-robot-2-line"></i>
                        </div>
                        <h3 class="feature-title">AI-Powered Job Matching</h3>
                        <p class="feature-desc">
                            Get personalized job and project recommendations using advanced AI matching—tailored to your skills, interests, and career
                            goals.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="feature-card glass-feature-card group cursor-pointer">
                        <div class="feature-icon-animated bg-gradient-to-tr from-violet-500 via-purple-500 to-fuchsia-500">
                            <i class="ri-graduation-cap-line"></i>
                        </div>
                        <h3 class="feature-title">Free Courses & Events</h3>
                        <p class="feature-desc">
                            Access a wide range of free online and offline courses, workshops, and events—empowering you to learn, grow, and connect
                            with the community year-round.
                        </p>
                    </div>
                    <!-- Feature 5 -->
                    <div class="feature-card glass-feature-card group cursor-pointer">
                        <div class="feature-icon-animated bg-gradient-to-tr from-rose-500 via-pink-500 to-purple-500">
                            <i class="ri-chat-4-line"></i>
                        </div>
                        <h3 class="feature-title">Real-time Community Chat</h3>
                        <p class="feature-desc">
                            Instantly connect, share, and collaborate with peers using our integrated chat system—join topic channels, direct message,
                            and build lasting relationships.
                        </p>
                    </div>
                    <!-- Feature 6 -->
                    <div class="feature-card glass-feature-card group cursor-pointer">
                        <div class="feature-icon-animated bg-gradient-to-tr from-orange-500 via-amber-500 to-yellow-500">
                            <i class="ri-mail-star-line"></i>
                        </div>
                        <h3 class="feature-title">AI Newsletter & Smart Hiring Suite</h3>
                        <p class="feature-desc">
                            Stay ahead with our AI-powered newsletter and help companies hire the best AI & Data talent using advanced recruitment
                            tools and analytics.
                        </p>
                    </div>
                    <!-- Feature 7 -->
                    <div class="feature-card glass-feature-card group cursor-pointer">
                        <div class="feature-icon-animated bg-gradient-to-tr from-indigo-500 via-blue-500 to-cyan-500">
                            <i class="ri-trophy-line"></i>
                        </div>
                        <h3 class="feature-title">Hackathons & Community Fun</h3>
                        <p class="feature-desc">
                            Compete in exciting hackathons, join fun challenges, and enjoy a vibrant, engaging community experience beyond just work
                            and learning.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section id="events" class="py-32 relative overflow-hidden">
        <!-- Modern animated circular gradient spots background -->
        <div class="absolute inset-0 -z-10">
            <div class="animated-spot spot1"></div>
            <div class="animated-spot spot2"></div>
            <div class="animated-spot spot3"></div>
            <div class="animated-spot spot4"></div>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center mb-20 fade-in">
                <h2 class="text-5xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-slate-900 to-slate-600 mb-6">Upcoming Events</h2>
                <p class="text-xl text-secondary max-w-3xl mx-auto">
                    Join our community events and connect with fellow AI and data professionals
                </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                <!-- Event Card 1 -->
                <div class="event-card group">
                    <div class="event-img-container">
                        <img src="https://readdy.ai/api/search-image?query=modern%20AI%20conference%20presentation%20with%20data%20visualization%20screens%2C%20professional%20speakers%20on%20stage%2C%20audience%20of%20tech%20professionals%2C%20clean%20corporate%20environment%2C%20indigo%20lighting%20accents%2C%20high-tech%20atmosphere&width=380&height=220&seq=event1&orientation=landscape"
                            alt="AI Summit 2024" class="event-img">
                        <div class="event-date-badge">
                            <span>DEC</span>
                            <span class="event-date-day">15</span>
                            <span class="event-date-year">2024</span>
                        </div>
                        <span class="event-type-badge-simple">Conference</span>
                    </div>
                    <div class="event-content">
                        <h3 class="event-title">AI Summit Egypt 2024</h3>
                        <p class="event-desc">
                            Join Egypt's largest AI conference featuring international speakers, workshops, and networking opportunities.
                        </p>
                        <div class="event-meta">
                            <div class="event-attendance">
                                <i class="ri-user-line"></i>
                                <span>247 attending</span>
                            </div>
                            <button class="event-join-btn">Join Event</button>
                        </div>
                    </div>
                </div>
                <!-- Event Card 2 -->
                <div class="event-card group">
                    <div class="event-img-container">
                        <img src="https://readdy.ai/api/search-image?query=data%20science%20workshop%20with%20laptops%2C%20coding%20screens%2C%20collaborative%20learning%20environment%2C%20modern%20classroom%20setup%2C%20participants%20working%20on%20data%20analysis%20projects%2C%20professional%20training%20atmosphere&width=350&height=200&seq=event2&orientation=landscape"
                            alt="Data Science Workshop" class="event-img">
                        <div class="event-date-badge">
                            <span>DEC</span>
                            <span class="event-date-day">20</span>
                            <span class="event-date-year">2024</span>
                        </div>
                        <span class="event-type-badge-simple">Workshop</span>
                    </div>
                    <div class="event-content">
                        <h3 class="event-title">Machine Learning Fundamentals</h3>
                        <p class="event-desc">
                            Hands-on workshop covering essential ML algorithms and practical implementation techniques.
                        </p>
                        <div class="event-meta">
                            <div class="event-attendance">
                                <i class="ri-user-line"></i>
                                <span>89 attending</span>
                            </div>
                            <button class="event-join-btn">Join Event</button>
                        </div>
                    </div>
                </div>
                <!-- Event Card 3 -->
                <div class="event-card group">
                    <div class="event-img-container">
                        <img src="https://readdy.ai/api/search-image?query=professional%20networking%20event%20with%20data%20professionals%2C%20casual%20meetup%20atmosphere%2C%20people%20discussing%20technology%2C%20modern%20venue%20with%20tech%20displays%2C%20collaborative%20environment%2C%20business%20networking&width=350&height=200&seq=event3&orientation=landscape"
                            alt="Networking Meetup" class="event-img">
                        <div class="event-date-badge">
                            <span>DEC</span>
                            <span class="event-date-day">28</span>
                            <span class="event-date-year">2024</span>
                        </div>
                        <span class="event-type-badge-simple">Meetup</span>
                    </div>
                    <div class="event-content">
                        <h3 class="event-title">Cairo Data Professionals Meetup</h3>
                        <p class="event-desc">
                            Monthly networking event for data professionals to share experiences and build connections.
                        </p>
                        <div class="event-meta">
                            <div class="event-attendance">
                                <i class="ri-user-line"></i>
                                <span>156 attending</span>
                            </div>
                            <button class="event-join-btn">Join Event</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-center mt-12">
                <a href="@auth{{ route('events.index') }}@else{{ route('login') }}@endauth" class="discover-events-btn">
                    <span>Discover All Events</span>
                    <i class="ri-arrow-right-line ml-2"></i>
                </a>
            </div>
        </div>
    </section>
    <section id="community" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 fade-in">
                <h2 class="text-4xl font-bold text-slate-900 mb-4">Community Activity</h2>
                <p class="text-xl text-secondary max-w-3xl mx-auto">
                    Stay updated with the latest discussions, insights, and knowledge sharing from our community
                </p>
            </div>
            <div class="grid lg:grid-cols-2 gap-8">
                <div class="space-y-6">
                    <div class="bg-white border border-slate-200 rounded-2xl p-6 fade-in">
                        <div class="flex items-start space-x-4">
                            <img src="https://readdy.ai/api/search-image?query=professional%20headshot%20of%20data%20scientist%2C%20middle%20eastern%20woman%2C%20confident%20expression%2C%20modern%20office%20background%2C%20professional%20attire%2C%20clean%20corporate%20portrait&width=48&height=48&seq=avatar1&orientation=squarish"
                                alt="Sarah Ahmed" class="w-12 h-12 rounded-full object-cover">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-2">
                                    <h4 class="font-semibold text-slate-900">Sarah Ahmed</h4>
                                    <span class="text-sm text-secondary">• 2 hours ago</span>
                                </div>
                                <p class="text-slate-700 mb-4">
                                    Just finished implementing a new recommendation system using collaborative filtering. The results are impressive -
                                    23% improvement in user engagement! Happy to share the approach if anyone's interested.
                                </p>
                                <div class="flex items-center space-x-6 text-sm text-secondary">
                                    <button class="flex items-center space-x-1 hover:text-primary transition-colors">
                                        <i class="ri-thumb-up-line"></i>
                                        <span>24</span>
                                    </button>
                                    <button class="flex items-center space-x-1 hover:text-primary transition-colors">
                                        <i class="ri-chat-3-line"></i>
                                        <span>8</span>
                                    </button>
                                    <button class="flex items-center space-x-1 hover:text-primary transition-colors">
                                        <i class="ri-share-line"></i>
                                        <span>Share</span>
                                    </button>
                                </div>
                                <div class="flex flex-wrap gap-2 mt-4">
                                    <span class="bg-primary/10 text-primary px-2 py-1 rounded-full text-xs">#MachineLearning</span>
                                    <span class="bg-primary/10 text-primary px-2 py-1 rounded-full text-xs">#RecommendationSystems</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-2xl p-6 fade-in">
                        <div class="flex items-start space-x-4">
                            <img src="https://readdy.ai/api/search-image?query=professional%20headshot%20of%20AI%20engineer%2C%20middle%20eastern%20man%2C%20glasses%2C%20confident%20smile%2C%20tech%20office%20background%2C%20business%20casual%20attire%2C%20modern%20portrait&width=48&height=48&seq=avatar2&orientation=squarish"
                                alt="Mohamed Hassan" class="w-12 h-12 rounded-full object-cover">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-2">
                                    <h4 class="font-semibold text-slate-900">Mohamed Hassan</h4>
                                    <span class="text-sm text-secondary">• 5 hours ago</span>
                                </div>
                                <p class="text-slate-700 mb-4">
                                    Looking for advice on scaling deep learning models for production. We're dealing with 100M+ parameters and need to
                                    optimize inference time. Any suggestions on model compression techniques?
                                </p>
                                <div class="flex items-center space-x-6 text-sm text-secondary">
                                    <button class="flex items-center space-x-1 hover:text-primary transition-colors">
                                        <i class="ri-thumb-up-line"></i>
                                        <span>18</span>
                                    </button>
                                    <button class="flex items-center space-x-1 hover:text-primary transition-colors">
                                        <i class="ri-chat-3-line"></i>
                                        <span>12</span>
                                    </button>
                                    <button class="flex items-center space-x-1 hover:text-primary transition-colors">
                                        <i class="ri-share-line"></i>
                                        <span>Share</span>
                                    </button>
                                </div>
                                <div class="flex flex-wrap gap-2 mt-4">
                                    <span class="bg-primary/10 text-primary px-2 py-1 rounded-full text-xs">#DeepLearning</span>
                                    <span class="bg-primary/10 text-primary px-2 py-1 rounded-full text-xs">#ModelOptimization</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="space-y-6">
                    <div class="bg-white border border-slate-200 rounded-2xl p-6 fade-in">
                        <div class="flex items-start space-x-4">
                            <img src="https://readdy.ai/api/search-image?query=professional%20headshot%20of%20data%20analyst%2C%20middle%20eastern%20woman%2C%20professional%20smile%2C%20modern%20workspace%20background%2C%20business%20attire%2C%20confident%20expression&width=48&height=48&seq=avatar3&orientation=squarish"
                                alt="Fatima El-Sayed" class="w-12 h-12 rounded-full object-cover">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-2">
                                    <h4 class="font-semibold text-slate-900">Fatima El-Sayed</h4>
                                    <span class="text-sm text-secondary">• 1 day ago</span>
                                </div>
                                <p class="text-slate-700 mb-4">
                                    Excited to share my latest project on predicting customer churn using ensemble methods. Achieved 94% accuracy with
                                    Random Forest + XGBoost combination. Code and dataset available on GitHub!
                                </p>
                                <div class="flex items-center space-x-6 text-sm text-secondary">
                                    <button class="flex items-center space-x-1 hover:text-primary transition-colors">
                                        <i class="ri-thumb-up-line"></i>
                                        <span>42</span>
                                    </button>
                                    <button class="flex items-center space-x-1 hover:text-primary transition-colors">
                                        <i class="ri-chat-3-line"></i>
                                        <span>15</span>
                                    </button>
                                    <button class="flex items-center space-x-1 hover:text-primary transition-colors">
                                        <i class="ri-share-line"></i>
                                        <span>Share</span>
                                    </button>
                                </div>
                                <div class="flex flex-wrap gap-2 mt-4">
                                    <span class="bg-primary/10 text-primary px-2 py-1 rounded-full text-xs">#DataScience</span>
                                    <span class="bg-primary/10 text-primary px-2 py-1 rounded-full text-xs">#CustomerAnalytics</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-2xl p-6 fade-in">
                        <div class="flex items-start space-x-4">
                            <img src="https://readdy.ai/api/search-image?query=professional%20headshot%20of%20machine%20learning%20engineer%2C%20middle%20eastern%20man%2C%20beard%2C%20confident%20expression%2C%20tech%20company%20office%20background%2C%20casual%20professional%20attire&width=48&height=48&seq=avatar4&orientation=squarish"
                                alt="Ahmed Mahmoud" class="w-12 h-12 rounded-full object-cover">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-2">
                                    <h4 class="font-semibold text-slate-900">Ahmed Mahmoud</h4>
                                    <span class="text-sm text-secondary">• 2 days ago</span>
                                </div>
                                <p class="text-slate-700 mb-4">
                                    Great discussion at yesterday's meetup about ethical AI! Key takeaway: We need more diverse datasets and
                                    transparent algorithms. Let's continue building responsible AI solutions in Egypt.
                                </p>
                                <div class="flex items-center space-x-6 text-sm text-secondary">
                                    <button class="flex items-center space-x-1 hover:text-primary transition-colors">
                                        <i class="ri-thumb-up-line"></i>
                                        <span>31</span>
                                    </button>
                                    <button class="flex items-center space-x-1 hover:text-primary transition-colors">
                                        <i class="ri-chat-3-line"></i>
                                        <span>9</span>
                                    </button>
                                    <button class="flex items-center space-x-1 hover:text-primary transition-colors">
                                        <i class="ri-share-line"></i>
                                        <span>Share</span>
                                    </button>
                                </div>
                                <div class="flex flex-wrap gap-2 mt-4">
                                    <span class="bg-primary/10 text-primary px-2 py-1 rounded-full text-xs">#EthicalAI</span>
                                    <span class="bg-primary/10 text-primary px-2 py-1 rounded-full text-xs">#ResponsibleAI</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-center mt-12">
                <a href="@auth{{ route('home') }}@else{{ route('login') }}@endauth" class="discover-events-btn">
                    <span>View Community Posts</span>
                    <i class="ri-arrow-right-line ml-2"></i>
                </a>
            </div>
        </div>
    </section>
    <section class="py-24 gradient-bg relative overflow-hidden">
        <div class="absolute inset-0">
            <div class="particle" style="top: 10%; left: 10%; animation-delay: 0s;"></div>
            <div class="particle" style="top: 20%; left: 80%; animation-delay: 1s;"></div>
            <div class="particle" style="top: 70%; left: 20%; animation-delay: 2s;"></div>
            <div class="particle" style="top: 60%; left: 90%; animation-delay: 3s;"></div>
            <div class="particle" style="top: 30%; left: 60%; animation-delay: 4s;"></div>
        </div>
        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="fade-in">
                <h2 class="text-4xl lg:text-5xl font-bold text-white mb-6">
                    Ready to Join Egypt's Leading Data Community?
                </h2>
                <p class="text-xl text-indigo-100 mb-12 leading-relaxed">
                    Connect with 2,800+ AI and data professionals, access exclusive events, discover career opportunities, and be part of Egypt's most
                    vibrant tech community.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('register') }}"
                        class="bg-white text-primary px-8 py-4 !rounded-button text-lg font-semibold hover:bg-slate-100 transition-all transform hover:scale-105 whitespace-nowrap">
                        Sign Up Now
                    </a>
                </div>
            </div>
        </div>
    </section>
    @include('components.footer')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Mobile menu functionality
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const mobileMenuOverlay = document.getElementById('mobile-menu-overlay');
            const mobileMenu = document.getElementById('mobile-menu');
            let isMenuOpen = false;

            function toggleMobileMenu() {
                isMenuOpen = !isMenuOpen;

                if (isMenuOpen) {
                    mobileMenuOverlay.classList.remove('hidden');
                    document.body.classList.add('menu-open');
                    // Trigger slide up animation
                    setTimeout(() => {
                        mobileMenu.classList.remove('translate-y-[120px]');
                    }, 10);
                    // Change menu icon to close
                    mobileMenuBtn.innerHTML = '<i class="ri-close-line text-xl"></i>';
                } else {
                    mobileMenu.classList.add('translate-y-[120px]');
                    document.body.classList.remove('menu-open');
                    // Change menu icon back to menu
                    mobileMenuBtn.innerHTML = '<i class="ri-menu-line text-xl"></i>';
                    // Hide overlay after animation
                    setTimeout(() => {
                        mobileMenuOverlay.classList.add('hidden');
                    }, 300);
                }
            }

            // Toggle menu on button click
            mobileMenuBtn.addEventListener('click', toggleMobileMenu);

            // Close menu when clicking overlay
            mobileMenuOverlay.addEventListener('click', (e) => {
                if (e.target === mobileMenuOverlay) {
                    toggleMobileMenu();
                }
            });

            // Close menu when clicking on menu links
            mobileMenuOverlay.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => {
                    if (isMenuOpen) {
                        toggleMobileMenu();
                    }
                });
            });

            // Scroll animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: "0px 0px -50px 0px",
            };
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add("visible");
                    }
                });
            }, observerOptions);
            document.querySelectorAll(".fade-in").forEach((el) => {
                observer.observe(el);
            });

            // Counter animations
            function animateCounter(element, target) {
                let current = 0;
                const increment = target / 100;
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    element.textContent = Math.floor(current).toLocaleString();
                }, 20);
            }
            const counters = document.querySelectorAll(".counter");
            const counterObserver = new IntersectionObserver(
                function(entries) {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            const target = parseInt(entry.target.dataset.target);
                            animateCounter(entry.target, target);
                            counterObserver.unobserve(entry.target);
                        }
                    });
                }, {
                    threshold: 0.5
                },
            );
            counters.forEach((counter) => {
                counterObserver.observe(counter);
            });

            // Navbar scroll effect
            const navbar = document.getElementById("navbar");
            let lastScroll = 0;
            window.addEventListener("scroll", () => {
                const currentScroll = window.pageYOffset;
                if (currentScroll > 0) {
                    navbar.classList.add(
                        "bg-white/40",
                        "backdrop-blur-xl",
                        "shadow-[0_8px_32px_rgba(0,0,0,0.08)]",
                    );
                } else {
                    navbar.classList.remove(
                        "bg-white/40",
                        "backdrop-blur-xl",
                        "shadow-[0_8px_32px_rgba(0,0,0,0.08)]",
                    );
                }
                lastScroll = currentScroll;
            });

            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
                anchor.addEventListener("click", function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute("href"));
                    if (target) {
                        target.scrollIntoView({
                            behavior: "smooth",
                            block: "start",
                        });
                    }
                });
            });

            // Circle hover functionality
            const listItems = document.querySelectorAll('[data-target]');
            const networkImage = document.getElementById('network-image');
            const knowledgeImage = document.getElementById('knowledge-image');
            const careerImage = document.getElementById('career-image');
            const networkLabels = document.getElementById('network-labels');
            const knowledgeLabels = document.getElementById('knowledge-labels');
            const careerLabels = document.getElementById('career-labels');
            const circleContent = document.getElementById('circle-content');
            const communityCircle = document.getElementById('community-circle');

            function hideAll() {
                // Hide all images and labels
                networkImage.style.opacity = '0';
                knowledgeImage.style.opacity = '0';
                careerImage.style.opacity = '0';
                networkLabels.classList.remove('show');
                knowledgeLabels.classList.remove('show');
                careerLabels.classList.remove('show');

                // Show default content
                circleContent.style.opacity = '1';
                communityCircle.style.boxShadow = '0 25px 50px -12px rgba(79, 70, 229, 0.25)';
            }

            function showContent(target) {
                // Hide default content immediately
                circleContent.style.opacity = '0';

                // Hide all images and labels first
                networkImage.style.opacity = '0';
                knowledgeImage.style.opacity = '0';
                careerImage.style.opacity = '0';
                networkLabels.classList.remove('show');
                knowledgeLabels.classList.remove('show');
                careerLabels.classList.remove('show');

                // Show specific content based on target
                switch (target) {
                    case 'network':
                        networkImage.style.opacity = '1';
                        networkLabels.classList.add('show');
                        break;
                    case 'knowledge':
                        knowledgeImage.style.opacity = '1';
                        knowledgeLabels.classList.add('show');
                        break;
                    case 'career':
                        careerImage.style.opacity = '1';
                        careerLabels.classList.add('show');
                        break;
                }

                // Enhanced shadow effect
                communityCircle.style.boxShadow = '0 0 40px rgba(79, 70, 229, 0.4)';
            }

            listItems.forEach(item => {
                item.addEventListener('mouseenter', function() {
                    const target = this.getAttribute('data-target');
                    showContent(target);
                });
                item.addEventListener('mouseleave', function() {
                    hideAll();
                });
            });

            // Initialize with default state
            hideAll();
        });

        function scrollToNextSection() {
            const heroSection = document.querySelector('section');
            const nextSection = heroSection.nextElementSibling;
            if (nextSection) {
                nextSection.scrollIntoView({
                    behavior: "smooth",
                    block: "start",
                });
            }
        }
    </script>
    <script>
        class ParticleNetwork {
            constructor(canvas) {
                this.canvas = canvas;
                this.ctx = canvas.getContext('2d');
                this.particles = [];
                this.connectionDistance = 150;
                this.particleCount = 100;

                this.resize();
                this.initParticles();
                this.animate();

                window.addEventListener('resize', () => this.resize());
            }

            resize() {
                const section = this.canvas.parentElement;
                this.canvas.width = section.offsetWidth;
                this.canvas.height = section.offsetHeight;
                this.initParticles();
            }

            initParticles() {
                this.particles = Array.from({
                    length: this.particleCount
                }, () => ({
                    x: Math.random() * this.canvas.width,
                    y: Math.random() * this.canvas.height,
                    vx: (Math.random() - 0.5) * 0.8,
                    vy: (Math.random() - 0.5) * 0.8
                }));
            }

            drawParticles() {
                this.ctx.fillStyle = 'rgba(100, 100, 100, 0.7)';
                this.particles.forEach(p => {
                    this.ctx.beginPath();
                    this.ctx.arc(p.x, p.y, 2, 0, Math.PI * 2);
                    this.ctx.fill();
                });
            }

            drawConnections() {
                this.ctx.strokeStyle = 'rgba(100, 100, 100, 0.1)';
                this.ctx.lineWidth = 0.5;

                for (let i = 0; i < this.particles.length; i++) {
                    for (let j = i + 1; j < this.particles.length; j++) {
                        const p1 = this.particles[i];
                        const p2 = this.particles[j];
                        const dx = p1.x - p2.x;
                        const dy = p1.y - p2.y;
                        const distance = Math.sqrt(dx * dx + dy * dy);

                        if (distance < this.connectionDistance) {
                            this.ctx.beginPath();
                            this.ctx.moveTo(p1.x, p1.y);
                            this.ctx.lineTo(p2.x, p2.y);
                            this.ctx.stroke();
                        }
                    }
                }
            }

            updateParticles() {
                this.particles.forEach(p => {
                    p.x += p.vx;
                    p.y += p.vy;

                    if (p.x <= 0 || p.x >= this.canvas.width) p.vx *= -1;
                    if (p.y <= 0 || p.y >= this.canvas.height) p.vy *= -1;
                });
            }

            animate() {
                this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                this.updateParticles();
                this.drawConnections();
                this.drawParticles();
                requestAnimationFrame(() => this.animate());
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const canvas = document.getElementById('particleCanvas');
            if (canvas) new ParticleNetwork(canvas);

            // Hero and section 2 animations
            const heroElements = document.querySelectorAll('.hero-animate');
            const heroBgElements = document.querySelectorAll('.hero-bg-animate');
            const section2Elements = document.querySelectorAll('.section2-animate, .section2-circle-animate');

            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, observerOptions);

            section2Elements.forEach(element => {
                observer.observe(element);
            });

            setTimeout(() => {
                heroElements.forEach(element => {
                    element.classList.add('visible');
                });
                heroBgElements.forEach(element => {
                    element.classList.add('visible');
                });
            }, 300);
        });
    </script>

</body>

</html>