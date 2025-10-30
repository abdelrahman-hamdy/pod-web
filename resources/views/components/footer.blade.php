<footer class="bg-slate-900 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
            <div>
                <div class="flex items-center space-x-2 mb-6">
                    <div class="w-12 h-8 flex items-center justify-center">
                        <img src="{{ asset('storage/assets/pod-logo.png') }}" alt="Logo" class="footer-pod-logo">
                    </div>
                    <span class="text-xl font-bold">People Of Data</span>
                </div>
                <p class="text-slate-400 leading-relaxed mb-6">
                    The leading community platform for data science and AI professionals in Egypt and MENA. 
                    Connect, learn, and grow your career with like-minded professionals.
                </p>
                <div class="flex space-x-4">
                    <!-- LinkedIn -->
                    <a href="https://www.linkedin.com/company/people-of-data-middle-east/" target="_blank" rel="noopener noreferrer" class="w-10 h-10 bg-slate-800 rounded-lg flex items-center justify-center hover:bg-primary transition-colors">
                        <i class="ri-linkedin-fill text-lg"></i>
                    </a>
                    <!-- TikTok -->
                    <a href="https://www.tiktok.com/@peopleofdata" target="_blank" rel="noopener noreferrer" class="w-10 h-10 bg-slate-800 rounded-lg flex items-center justify-center hover:bg-primary transition-colors">
                        <i class="ri-tiktok-fill text-lg"></i>
                    </a>
                    <!-- Telegram -->
                    <a href="https://t.me/peopleofdataa" target="_blank" rel="noopener noreferrer" class="w-10 h-10 bg-slate-800 rounded-lg flex items-center justify-center hover:bg-primary transition-colors">
                        <i class="ri-telegram-2-fill text-lg"></i>
                    </a>
                    <!-- Instagram -->
                    <a href="https://www.instagram.com/peopleofdata2.0" target="_blank" rel="noopener noreferrer" class="w-10 h-10 bg-slate-800 rounded-lg flex items-center justify-center hover:bg-primary transition-colors">
                        <i class="ri-instagram-line text-lg"></i>
                    </a>
                </div>
            </div>
            <div>
                <h3 class="text-lg font-semibold mb-6">Community</h3>
                <ul class="space-y-4 text-slate-400">
                    <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">Posts</a></li>
                    <li><a href="{{ route('events.index') }}" class="hover:text-white transition-colors">Events</a></li>
                    <li><a href="{{ route('hackathons.index') }}" class="hover:text-white transition-colors">Hackathons</a></li>
                    <li><a href="{{ route('profile.show') }}" class="hover:text-white transition-colors">Profile</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-lg font-semibold mb-6">Opportunities</h3>
                <ul class="space-y-4 text-slate-400">
                    <li><a href="{{ route('jobs.index') }}" class="hover:text-white transition-colors">Jobs</a></li>
                    <li><a href="{{ route('internships.index') }}" class="hover:text-white transition-colors">Internships</a></li>
                    <li><a href="{{ route('events.index') }}" class="hover:text-white transition-colors">Events</a></li>
                    <li><a href="{{ route('hackathons.index') }}" class="hover:text-white transition-colors">Hackathons</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-lg font-semibold mb-6">Stay Updated</h3>
                <p class="text-slate-400 mb-4">
                    Get the latest news, events, and job opportunities delivered to your inbox.
                </p>
                @guest
                    <form action="{{ route('newsletter.subscribe') }}" method="POST" class="newsletter-form">
                        @csrf
                        <input type="email" name="email" placeholder="Enter your email" required>
                        <button type="submit">Subscribe</button>
                    </form>
                @else
                    @if (Auth::user()->newsletterSubscription)
                        <div class="flex items-center gap-2 p-4 bg-slate-800/50 rounded-xl border border-green-500/20">
                            <i class="ri-checkbox-circle-fill text-green-500 text-xl"></i>
                            <p class="text-green-400 text-sm">You're subscribed to our newsletter!</p>
                        </div>
                    @else
                        <form action="{{ route('newsletter.subscribe') }}" method="POST">
                            @csrf
                            <input type="hidden" name="email" value="{{ Auth::user()->email }}">
                            <button type="submit" class="newsletter-btn-logged-in group w-full">
                                <span class="flex items-center justify-center gap-2">
                                    <i class="ri-mail-add-line text-lg"></i>
                                    <span>Subscribe to Newsletter</span>
                                </span>
                                <span class="text-xs opacity-70 mt-1 block">{{ Auth::user()->email }}</span>
                            </button>
                        </form>
                    @endif
                @endguest
            </div>
        </div>
        <div class="border-t border-slate-800 pt-8">
            <div class="flex justify-center">
                <p class="text-slate-400 text-sm">
                    &copy; {{ date('Y') }} People Of Data. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</footer>

<style>
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

/* Newsletter button for logged-in users */
.newsletter-btn-logged-in {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    color: #fff;
    border: none;
    border-radius: 0.75rem;
    padding: 1rem 1.5rem;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.3), 0 2px 4px -1px rgba(79, 70, 229, 0.2);
    position: relative;
    overflow: hidden;
}

.newsletter-btn-logged-in::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.newsletter-btn-logged-in:hover::before {
    left: 100%;
}

.newsletter-btn-logged-in:hover {
    background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%);
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4), 0 4px 6px -2px rgba(79, 70, 229, 0.3);
}

.newsletter-btn-logged-in:active {
    transform: translateY(0);
    box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.3), 0 2px 4px -1px rgba(79, 70, 229, 0.2);
}

.newsletter-btn-logged-in i {
    transition: transform 0.3s;
}

.newsletter-btn-logged-in:hover i {
    transform: scale(1.1);
}
</style>
