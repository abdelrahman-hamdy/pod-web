@extends('layouts.app')

@section('title', ($user->name ?? 'User') . ' - Profile - People Of Data')

@section('content')

<!-- Alert Container -->
<div id="alert-container" class="fixed top-20 right-4 z-[10000] max-w-md w-full"></div>

<div class="w-full">
    <!-- Profile Header - Full Width -->
    <div class="max-w-7xl mx-auto mb-8">
        <div class="bg-gradient-to-br from-indigo-50 via-white to-purple-50 rounded-xl shadow-sm border border-indigo-100 p-6">
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
                <!-- Left: Avatar & Basic Info -->
                <div class="flex items-end gap-6">
                    <!-- Avatar -->
                    <div class="relative">
                        <div class="w-32 h-32 rounded-full ring-4 ring-white shadow-lg">
                            <x-avatar 
                                :src="$user->avatar ?? null"
                                :name="$user->name ?? 'User'"
                                size="lg"
                                class="w-full h-full"
                                :color="$user->avatar_color ?? null" />
                        </div>
                    </div>
                    
                    <!-- User Info -->
                    <div class="flex-1 pb-2">
                        <h1 class="text-3xl lg:text-4xl font-bold text-slate-800 mb-2 flex items-center">{{ $user->name ?? 'User Name' }}<x-business-badge :user="$user" /></h1>
                        @if($user->title)
                            <p class="text-lg text-slate-600 mb-2">{{ $user->title }}</p>
                        @endif
                        
                        <!-- Quick Info -->
                        <div class="flex flex-wrap items-center gap-4 text-sm text-slate-600">
                            @if($user->city)
                                <div class="flex items-center space-x-1">
                                    <i class="ri-map-pin-line text-indigo-500"></i>
                                    <span>{{ $user->city }}{{ $user->country ? ', ' . $user->country : '' }}</span>
                                </div>
                            @endif
                            @if($user->company)
                                <div class="flex items-center space-x-1">
                                    <i class="ri-building-line text-indigo-500"></i>
                                    <span>{{ $user->company }}</span>
                                </div>
                            @endif
                            <div class="flex items-center space-x-1">
                                <i class="ri-calendar-line text-indigo-500"></i>
                                <span>Joined {{ $user->created_at->format('M Y') }}</span>
                            </div>
                            @if($user->experience_level)
                                <div class="flex items-center space-x-1">
                                    <i class="ri-star-line text-indigo-500"></i>
                                    <span class="capitalize">{{ str_replace('_', ' ', $user->experience_level->value) }} Level</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Right: Action Button -->
                <div class="flex-shrink-0 mb-2">
                    @if($user->id !== auth()->id())
                        <a href="{{ url(config('chatify.routes.prefix') . '/' . $user->id) }}" 
                           class="bg-indigo-600 text-white px-6 py-3 rounded-xl hover:bg-indigo-700 transition-all shadow-md hover:shadow-lg text-sm font-medium inline-flex items-center space-x-2">
                            <i class="ri-message-3-line"></i>
                            <span>Send Message</span>
                        </a>
                    @else
                        <x-forms.button 
                            href="{{ route('profile.edit') }}"
                            variant="primary"
                            icon="ri-edit-line"
                            size="sm">
                            Edit Profile
                        </x-forms.button>
                    @endif
                </div>
            </div>
            
            <!-- Bio Section -->
            @if($user->bio)
                <div class="mt-6 pt-6 border-t border-slate-200">
                    <h3 class="text-sm font-semibold text-slate-800 mb-2 uppercase tracking-wide">About</h3>
                    <p class="text-slate-700 leading-relaxed">{{ $user->bio }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="flex flex-col lg:flex-row lg:gap-8 gap-6 w-full justify-center">
        <!-- Main Content Area -->
        <div class="flex-1 w-full lg:max-w-3xl min-w-0 lg:flex-shrink-0">
            <div class="space-y-6">
                <!-- Experience -->
                @if($user->id === auth()->id() || $user->experiences->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 lg:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-slate-800">Experience</h2>
                        @if($user->id === auth()->id())
                            <button onclick="openExperienceModal()" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium flex items-center space-x-1">
                                <i class="ri-add-line"></i>
                                <span>Add Experience</span>
                            </button>
                        @endif
                    </div>
                    <div class="space-y-6">
                        @forelse($user->experiences as $experience)
                            <div class="flex space-x-4 group relative">
                                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="ri-building-line text-indigo-600"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-slate-800">{{ $experience->title }}</h3>
                                            @if($experience->company_url)
                                                <a href="{{ $experience->company_url }}" target="_blank" class="text-indigo-600 font-medium hover:text-indigo-700">
                                                    {{ $experience->company }}
                                                </a>
                                            @else
                                                <p class="text-indigo-600 font-medium">{{ $experience->company }}</p>
                                            @endif
                                            <p class="text-sm text-slate-500 mb-2">{{ $experience->duration }}</p>
                                            @if($experience->description)
                                                <p class="text-slate-600 text-sm">{{ $experience->description }}</p>
                                            @endif
                                        </div>
                                        @if($user->id === auth()->id())
                                            <div class="opacity-0 group-hover:opacity-100 transition-opacity flex space-x-2">
                                                <button onclick="editExperience({{ $experience->id }})" class="text-indigo-600 hover:text-indigo-700" title="Edit">
                                                    <i class="ri-pencil-line"></i>
                                                </button>
                                                <button onclick="deleteExperience({{ $experience->id }})" class="text-red-600 hover:text-red-700" title="Delete">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-slate-400 italic text-center py-8">No experience added yet.</p>
                        @endforelse
                    </div>
                </div>
                @endif

                <!-- Portfolio -->
                @if($user->id === auth()->id() || $user->portfolios->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 lg:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-slate-800">Portfolio</h2>
                        @if($user->id === auth()->id())
                            <button onclick="openPortfolioModal()" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium flex items-center space-x-1">
                                <i class="ri-add-line"></i>
                                <span>Add Project</span>
                            </button>
                        @endif
                    </div>
                    <div class="space-y-4">
                        @forelse($user->portfolios as $portfolio)
                            <div class="group relative border border-slate-200 rounded-lg p-4 hover:border-indigo-300 transition-colors">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-slate-800 mb-1">{{ $portfolio->title }}</h3>
                                        <a href="{{ $portfolio->url }}" target="_blank" class="text-indigo-600 hover:text-indigo-700 text-sm mb-2 inline-block">
                                            {{ $portfolio->url }} <i class="ri-external-link-line"></i>
                                        </a>
                                        @if($portfolio->description)
                                            <p class="text-slate-600 text-sm">{{ $portfolio->description }}</p>
                                        @endif
                                    </div>
                                    @if($user->id === auth()->id())
                                        <div class="opacity-0 group-hover:opacity-100 transition-opacity flex space-x-2 ml-4">
                                            <button onclick="editPortfolio({{ $portfolio->id }})" class="text-indigo-600 hover:text-indigo-700" title="Edit">
                                                <i class="ri-pencil-line"></i>
                                            </button>
                                            <button onclick="deletePortfolio({{ $portfolio->id }})" class="text-red-600 hover:text-red-700" title="Delete">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-slate-400 italic text-center py-8">No projects added yet.</p>
                        @endforelse
                    </div>
                </div>
                @endif

                <!-- Recent Posts -->
                <h2 class="text-lg font-semibold text-slate-800 mb-4">{{ $user->name }}'s Posts</h2>
                @php
                    $initialPosts = $user->posts()
                        ->with(['user', 'likes' => function ($query) {
                            $query->where('user_id', auth()->id());
                        }])
                        ->latest()
                        ->paginate(5);
                @endphp
                <div id="profile-posts-container"
                     class="space-y-6"
                     data-user-id="{{ $user->id }}"
                     data-next-page-url="{{ $initialPosts->nextPageUrl() ? (request()->url() . '?posts_page=2') : '' }}">
                    @forelse($initialPosts as $post)
                        @php
                            $post->is_liked = $post->likes->isNotEmpty();
                        @endphp
                        <x-post-card :post="$post" />
                    @empty
                        <p class="text-slate-400 italic text-center py-8">No posts yet.</p>
                    @endforelse
                </div>
                <div id="profile-posts-loader" class="flex items-center justify-center py-4 hidden">
                    <span class="text-slate-400 text-sm">Loading more posts...</span>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="w-full lg:w-80 lg:flex-shrink-0 min-w-0">
            <div class="space-y-4 lg:space-y-6 w-full">
                <!-- Client Conversion Request Widget (only for regular users viewing their own profile, or client users) -->
                @if(auth()->check() && auth()->id() === $user->id && ($user->isRegularUser() || $user->isClient()))
                    @php
                        $pendingRequest = $user->clientConversionRequests()->where('status', 'pending')->first();
                        $rejectedRequest = $user->clientConversionRequests()->where('status', 'rejected')->latest()->first();
                        $approvedRequest = $user->clientConversionRequests()->where('status', 'approved')->latest()->first();
                    @endphp
                    
                    <!-- Upgrade to Business Account Widget -->
                    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 lg:p-6">
                        @if($user->isClient())
                            <div class="text-center mb-5">
                                <p class="text-base font-medium text-slate-700 max-w-md mx-auto">
                                    You are now eligible for the following business features:
                                </p>
                            </div>
                        @else
                            <div class="text-center mb-5">
                                <!-- Revenue illustration placeholder - SVG will be provided here -->
                                <div class="w-24 h-24 mx-auto mb-4 bg-emerald-50 rounded-2xl flex items-center justify-center">
                                    <i class="ri-building-4-line text-emerald-600 text-4xl"></i>
                                    <!-- TODO: Replace with provided SVG illustration -->
                                </div>
                                <h3 class="text-xl font-bold text-slate-900 mb-2">Upgrade to Business Account</h3>
                                <p class="text-sm text-slate-600 max-w-md mx-auto">
                                    Transform your profile into a powerful business account and unlock exclusive features designed to help you grow your business.
                                </p>
                            </div>
                        @endif
                        
                        <div class="space-y-2.5 mb-6">
                            <div class="flex items-center space-x-3 p-2.5 rounded-lg hover:bg-slate-50 transition-colors">
                                <div class="flex-shrink-0 w-6 h-6 bg-emerald-100 rounded-full flex items-center justify-center">
                                    <i class="ri-check-line text-emerald-600 text-xs font-bold"></i>
                                </div>
                                <span class="text-sm text-slate-700 font-medium flex-1">Create and manage events</span>
                            </div>
                            <div class="flex items-center space-x-3 p-2.5 rounded-lg hover:bg-slate-50 transition-colors">
                                <div class="flex-shrink-0 w-6 h-6 bg-emerald-100 rounded-full flex items-center justify-center">
                                    <i class="ri-check-line text-emerald-600 text-xs font-bold"></i>
                                </div>
                                <span class="text-sm text-slate-700 font-medium flex-1">Post job opportunities</span>
                            </div>
                            <div class="flex items-center space-x-3 p-2.5 rounded-lg hover:bg-slate-50 transition-colors">
                                <div class="flex-shrink-0 w-6 h-6 bg-emerald-100 rounded-full flex items-center justify-center">
                                    <i class="ri-check-line text-emerald-600 text-xs font-bold"></i>
                                </div>
                                <span class="text-sm text-slate-700 font-medium flex-1">Host hackathons</span>
                            </div>
                            <div class="flex items-center space-x-3 p-2.5 rounded-lg hover:bg-slate-50 transition-colors">
                                <div class="flex-shrink-0 w-6 h-6 bg-emerald-100 rounded-full flex items-center justify-center">
                                    <i class="ri-check-line text-emerald-600 text-xs font-bold"></i>
                                </div>
                                <span class="text-sm text-slate-700 font-medium flex-1">Access AI business tools</span>
                            </div>
                        </div>
                        
                        @if($user->isClient())
                            <button disabled
                                    class="w-full bg-emerald-200 text-emerald-800 px-6 py-3 rounded-lg cursor-default font-semibold text-sm flex items-center justify-center space-x-2">
                                <i class="ri-checkbox-circle-line"></i>
                                <span>Business Account Active</span>
                            </button>
                        @elseif($pendingRequest)
                            <button disabled
                                    class="w-full bg-slate-300 text-slate-600 px-6 py-3 rounded-lg cursor-not-allowed font-semibold text-sm flex items-center justify-center space-x-2">
                                <i class="ri-time-line"></i>
                                <span>Request Pending</span>
                            </button>
                        @else
                            <button onclick="requestClientConversion()" 
                                    class="w-full bg-emerald-600 text-white px-6 py-3 rounded-lg hover:bg-emerald-700 transition-colors font-semibold text-sm flex items-center justify-center space-x-2 group">
                                <span>{{ $rejectedRequest ? 'Submit New Request' : 'Request Business Account' }}</span>
                                <i class="ri-arrow-right-line group-hover:translate-x-1 transition-transform"></i>
                            </button>
                        @endif

                        <!-- Status Label at Bottom -->
                        @if($user->isClient())
                            <!-- Hide all status labels for active clients -->
                        @elseif($pendingRequest)
                            <div class="mt-4 pt-4 border-t border-slate-200">
                                <div class="flex items-center justify-center space-x-2 px-4 py-2 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    <i class="ri-time-line text-yellow-600"></i>
                                    <span class="text-sm font-medium text-yellow-800">Request Under Review</span>
                                </div>
                                <p class="text-xs text-slate-500 text-center mt-2">
                                    Submitted on {{ $pendingRequest->created_at->format('M d, Y') }}
                                </p>
                            </div>
                        @elseif($rejectedRequest)
                            <div class="mt-4 pt-4 border-t border-slate-200">
                                <div class="flex items-center justify-center space-x-2 px-4 py-2 bg-red-50 border border-red-200 rounded-lg">
                                    <i class="ri-close-circle-line text-red-600"></i>
                                    <span class="text-sm font-medium text-red-800">Previous Request Rejected</span>
                                </div>
                                @if($rejectedRequest->admin_notes)
                                    <p class="text-xs text-slate-600 mt-2 text-center">
                                        <span class="font-medium">Reason:</span> {{ $rejectedRequest->admin_notes }}
                                    </p>
                                @endif
                                <p class="text-xs text-slate-500 text-center mt-1">
                                    Reviewed on {{ $rejectedRequest->reviewed_at ? $rejectedRequest->reviewed_at->format('M d, Y') : 'N/A' }}
                                </p>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Skills -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 lg:p-6">
                    <h3 class="font-semibold text-slate-800 mb-3 lg:mb-4">Skills</h3>
                    @if($user->skills && count($user->skills) > 0)
                        <div class="flex flex-wrap gap-2">
                            @foreach($user->skills as $skill)
                                <span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-sm font-medium">
                                    {{ $skill }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-slate-400 italic">No skills listed.</p>
                    @endif
                </div>

                <!-- Contact Info -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 lg:p-6">
                    <h3 class="font-semibold text-slate-800 mb-3 lg:mb-4">Contact Information</h3>
                    <div class="space-y-3 lg:space-y-4">
                        <div class="flex items-center space-x-2">
                            <i class="ri-mail-line text-slate-400"></i>
                            <span class="text-sm text-slate-600">{{ $user->email }}</span>
                        </div>
                        @if($user->phone)
                            <div class="flex items-center space-x-2">
                                <i class="ri-phone-line text-slate-400"></i>
                                <span class="text-sm text-slate-600">{{ $user->phone }}</span>
                            </div>
                        @endif
                        @if($user->linkedin_url)
                            <div class="flex items-center space-x-2">
                                <i class="ri-linkedin-line text-slate-400"></i>
                                <a href="{{ $user->linkedin_url }}" class="text-sm text-indigo-600 hover:text-indigo-700" target="_blank">
                                    LinkedIn Profile
                                </a>
                            </div>
                        @endif
                        @if($user->github_url)
                            <div class="flex items-center space-x-2">
                                <i class="ri-github-line text-slate-400"></i>
                                <a href="{{ $user->github_url }}" class="text-sm text-indigo-600 hover:text-indigo-700" target="_blank">
                                    GitHub Profile
                                </a>
                            </div>
                        @endif
                        @if($user->website_url)
                            <div class="flex items-center space-x-2">
                                <i class="ri-global-line text-slate-400"></i>
                                <a href="{{ $user->website_url }}" class="text-sm text-indigo-600 hover:text-indigo-700" target="_blank">
                                    Personal Website
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Stats -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 lg:p-6">
                    <h3 class="font-semibold text-slate-800 mb-3 lg:mb-4">Statistics</h3>
                    <div class="space-y-3 lg:space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Posts</span>
                            <span class="text-sm font-semibold text-slate-800">{{ $user->posts()->count() }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Events Attended</span>
                            <span class="text-sm font-semibold text-slate-800">{{ $user->eventRegistrations()->count() }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Jobs Applied</span>
                            <span class="text-sm font-semibold text-slate-800">{{ $user->jobApplications()->count() }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Profile Views</span>
                            <span class="text-sm font-semibold text-slate-800">{{ $user->profile_views ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Experience Modal -->
<div id="experience-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[999] flex items-center justify-center p-4 !my-0">
    <div class="bg-white rounded-xl shadow-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <h3 class="text-xl font-semibold text-slate-800 mb-6" id="modal-title">Add Experience</h3>
            <form id="experience-form">
                @csrf
                <input type="hidden" name="experience_id" id="experience-id">
                
                <div class="space-y-4">
                    <!-- Job Title -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Job Title *</label>
                        <input type="text" name="title" id="experience-title" required
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Company -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Company *</label>
                            <input type="text" name="company" id="experience-company" required
                                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        
                        <!-- Company URL -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Company URL</label>
                            <input type="url" name="company_url" id="experience-company-url"
                                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                   placeholder="https://">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Start Date -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Start Date *</label>
                            <input type="month" name="start_date" id="experience-start-date" required
                                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        
                        <!-- End Date -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">End Date</label>
                            <div class="flex items-center space-x-2">
                                <input type="month" name="end_date" id="experience-end-date" 
                                       class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:bg-slate-50 disabled:text-slate-400"
                                       disabled>
                                <label class="flex items-center text-sm text-slate-600 whitespace-nowrap">
                                    <input type="hidden" name="is_current" value="0">
                                    <input type="checkbox" name="is_current" id="experience-current" value="1"
                                           onchange="toggleEndDate()"
                                           class="mr-2 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    Current Job
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Description</label>
                        <textarea name="description" id="experience-description" rows="4"
                                  class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>
                </div>
                
                <div class="flex items-center justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeExperienceModal()" 
                            class="px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        Save Experience
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Confirmation Modal for Client Conversion -->
<x-confirmation-modal id="client-conversion-modal" />

<!-- Comment Modal -->
<x-comment-modal />

<!-- Portfolio Modal -->
<div id="portfolio-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[999] flex items-center justify-center p-4 !my-0">
    <div class="bg-white rounded-xl shadow-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <h3 class="text-xl font-semibold text-slate-800 mb-6" id="portfolio-modal-title">Add Project</h3>
            <form id="portfolio-form">
                @csrf
                <input type="hidden" name="portfolio_id" id="portfolio-id">
                
                <div class="space-y-4">
                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Title *</label>
                        <input type="text" name="title" id="portfolio-title" required
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <!-- URL -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">URL *</label>
                        <input type="url" name="url" id="portfolio-url" required
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               placeholder="https://">
                    </div>
                    
                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Description</label>
                        <textarea name="description" id="portfolio-description" rows="4"
                                  class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>
                </div>
                
                <div class="flex items-center justify-end space-x-3 mt-6">
                    <button type="button" onclick="closePortfolioModal()" 
                            class="px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        Save Project
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<!-- Comment Manager Script (must load before post-interactions) -->
<script src="{{ asset('js/comment-manager.js') }}"></script>
<!-- Post Interactions Script -->
<script src="{{ asset('js/post-interactions.js') }}"></script>
<style>
    .like-btn:hover {
        color: #ef4444 !important;
    }
    .like-btn.text-red-500:hover {
        color: #dc2626 !important;
    }
</style>
<script>
    // Infinite scroll for Recent Posts
    (function() {
        const container = document.getElementById('profile-posts-container');
        if (!container) return;

        const loader = document.getElementById('profile-posts-loader');
        const userId = container.getAttribute('data-user-id');
        let nextPage = 2; // we rendered page 1 initially
        let loading = false;
        let hasMore = !!container.getAttribute('data-next-page-url');

        async function loadMorePosts() {
            if (loading || !hasMore) return;
            loading = true;
            loader.classList.remove('hidden');

            try {
                const url = `{{ url('/profile') }}/${userId}/posts?page=${nextPage}&per_page=5`;
                const resp = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await resp.json();
                if (data?.html) {
                    const temp = document.createElement('div');
                    temp.innerHTML = data.html;
                    while (temp.firstChild) {
                        container.appendChild(temp.firstChild);
                    }
                }
                if (data?.has_more && data?.next_page) {
                    nextPage = data.next_page;
                    hasMore = true;
                } else {
                    hasMore = false;
                }
            } catch (e) {
                console.error('Failed to load more posts', e);
            } finally {
                loader.classList.add('hidden');
                loading = false;
            }
        }

        function onScroll() {
            const rect = container.getBoundingClientRect();
            const isNearBottom = rect.bottom - window.innerHeight < 300; // 300px threshold
            if (isNearBottom) {
                loadMorePosts();
            }
        }

        // Attach scroll listener
        window.addEventListener('scroll', onScroll, { passive: true });
    })();
    // Client conversion request function
    function requestClientConversion() {
        openConfirmationModal(
            'client-conversion-modal',
            'Request Business Account',
            'You will be redirected to a form where you can provide your company information to request a business account upgrade. This request will be reviewed by our administration team.',
            function() {
                window.location.href = '{{ route("client.request") }}';
            }
        );
    }

    // Custom alert function
    function showAlert(type, message, title = null) {
        const alertContainer = document.getElementById('alert-container');
        const alertDiv = document.createElement('div');
        
        const typeClasses = {
            'error': 'bg-red-50 border-red-200 text-red-800',
            'success': 'bg-green-50 border-green-200 text-green-800',
            'warning': 'bg-yellow-50 border-yellow-200 text-yellow-800',
            'info': 'bg-blue-50 border-blue-200 text-blue-800'
        };
        
        const icons = {
            'error': 'ri-error-warning-line text-red-500',
            'success': 'ri-check-circle-line text-green-500',
            'warning': 'ri-alert-line text-yellow-500',
            'info': 'ri-information-line text-blue-500'
        };
        
        alertDiv.className = `border rounded-lg p-4 mb-3 transform transition-all duration-300 ${typeClasses[type]}`;
        alertDiv.innerHTML = `
            <div class="flex items-start">
                <i class="${icons[type]} text-xl mr-3"></i>
                <div class="flex-1">
                    ${title ? `<h3 class="font-semibold mb-1">${title}</h3>` : ''}
                    <p class="text-sm">${message}</p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-slate-400 hover:text-slate-600">
                    <i class="ri-close-line"></i>
                </button>
            </div>
        `;
        
        alertContainer.appendChild(alertDiv);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            alertDiv.style.opacity = '0';
            alertDiv.style.transform = 'translateY(-10px)';
            setTimeout(() => alertDiv.remove(), 300);
        }, 5000);
    }
    
    function toggleEndDate() {
        const isCurrent = document.getElementById('experience-current').checked;
        const endDateInput = document.getElementById('experience-end-date');
        
        if (isCurrent) {
            endDateInput.disabled = true;
            endDateInput.value = '';
        } else {
            endDateInput.disabled = false;
        }
    }
    
    function openExperienceModal(experienceId = null) {
        const modal = document.getElementById('experience-modal');
        const form = document.getElementById('experience-form');
        const title = document.getElementById('modal-title');
        
        if (experienceId) {
            title.textContent = 'Edit Experience';
            document.getElementById('experience-id').value = experienceId;
            
            // Load experience data
            fetch(`/profile/experiences/${experienceId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const exp = data.experience;
                        document.getElementById('experience-title').value = exp.title;
                        document.getElementById('experience-company').value = exp.company;
                        document.getElementById('experience-company-url').value = exp.company_url || '';
                        document.getElementById('experience-start-date').value = exp.start_date;
                        document.getElementById('experience-end-date').value = exp.end_date || '';
                        document.getElementById('experience-current').checked = exp.is_current;
                        document.getElementById('experience-description').value = exp.description || '';
                        toggleEndDate();
                    }
                });
        } else {
            title.textContent = 'Add Experience';
            form.reset();
            document.getElementById('experience-id').value = '';
            document.getElementById('experience-current').checked = false;
            toggleEndDate();
        }
        
        modal.classList.remove('hidden');
    }
    
    function closeExperienceModal() {
        const modal = document.getElementById('experience-modal');
        modal.classList.add('hidden');
    }
    
    function editExperience(id) {
        openExperienceModal(id);
    }
    
    function deleteExperience(id) {
        if (!confirm('Are you sure you want to delete this experience?')) return;
        
        fetch(`/profile/experiences/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'Experience deleted successfully');
                location.reload();
            } else {
                showAlert('error', data.message || 'Failed to delete experience');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Failed to delete experience. Please try again.');
        });
    }
    
    document.getElementById('experience-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const experienceId = formData.get('experience_id');
        const url = experienceId 
            ? `/profile/experiences/${experienceId}`
            : '/profile/experiences';
        
        // For PUT requests, we need to use POST with _method spoofing
        if (experienceId) {
            formData.append('_method', 'PUT');
        }
        
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(async response => {
            const data = await response.json();
            if (response.ok && data.success) {
                showAlert('success', 'Experience saved successfully!');
                setTimeout(() => location.reload(), 1000);
            } else {
                // Handle validation errors
                if (data.errors) {
                    let errorMessages = [];
                    Object.keys(data.errors).forEach(field => {
                        errorMessages.push(`${field}: ${data.errors[field].join(', ')}`);
                    });
                    showAlert('error', errorMessages.join(' | '), 'Validation Error');
                } else {
                    showAlert('error', data.message || 'Failed to save experience. Please try again.');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Failed to save experience. Please try again.');
        });
    });
    
    // Close modal on background click
    document.getElementById('experience-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeExperienceModal();
        }
    });
    
    // Portfolio functions
    function openPortfolioModal(portfolioId = null) {
        const modal = document.getElementById('portfolio-modal');
        const form = document.getElementById('portfolio-form');
        const title = document.getElementById('portfolio-modal-title');
        
        if (portfolioId) {
            title.textContent = 'Edit Project';
            document.getElementById('portfolio-id').value = portfolioId;
            
            // Load portfolio data
            fetch(`/profile/portfolios/${portfolioId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const portfolio = data.portfolio;
                        document.getElementById('portfolio-title').value = portfolio.title;
                        document.getElementById('portfolio-url').value = portfolio.url;
                        document.getElementById('portfolio-description').value = portfolio.description || '';
                    }
                });
        } else {
            title.textContent = 'Add Project';
            form.reset();
            document.getElementById('portfolio-id').value = '';
        }
        
        modal.classList.remove('hidden');
    }
    
    function closePortfolioModal() {
        const modal = document.getElementById('portfolio-modal');
        modal.classList.add('hidden');
    }
    
    function editPortfolio(id) {
        openPortfolioModal(id);
    }
    
    function deletePortfolio(id) {
        if (!confirm('Are you sure you want to delete this project?')) return;
        
        fetch(`/profile/portfolios/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'Project deleted successfully');
                location.reload();
            } else {
                showAlert('error', data.message || 'Failed to delete project');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Failed to delete project. Please try again.');
        });
    }
    
    document.getElementById('portfolio-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const portfolioId = formData.get('portfolio_id');
        const url = portfolioId 
            ? `/profile/portfolios/${portfolioId}`
            : '/profile/portfolios';
        
        // For PUT requests, we need to use POST with _method spoofing
        if (portfolioId) {
            formData.append('_method', 'PUT');
        }
        
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(async response => {
            const data = await response.json();
            if (response.ok && data.success) {
                showAlert('success', 'Project saved successfully!');
                setTimeout(() => location.reload(), 1000);
            } else {
                // Handle validation errors
                if (data.errors) {
                    let errorMessages = [];
                    Object.keys(data.errors).forEach(field => {
                        errorMessages.push(`${field}: ${data.errors[field].join(', ')}`);
                    });
                    showAlert('error', errorMessages.join(' | '), 'Validation Error');
                } else {
                    showAlert('error', data.message || 'Failed to save project. Please try again.');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Failed to save project. Please try again.');
        });
    });
    
    // Close portfolio modal on background click
    document.getElementById('portfolio-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closePortfolioModal();
        }
    });
</script>
@endpush

@endsection