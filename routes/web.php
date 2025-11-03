<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HackathonController;
use App\Http\Controllers\InternshipApplicationController;
use App\Http\Controllers\JobListingController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::get('/', function () {
    return view('index');
})->name('index');

// Public share counter (silent)
Route::post('/posts/{post}/share', [PostController::class, 'share'])->name('posts.share');
// Public share redirect (GET)
Route::get('/posts/{post}/share-redirect', [PostController::class, 'shareRedirect'])->name('posts.share-redirect');
// Public share ping (GET)
Route::get('/posts/{post}/share-ping', [PostController::class, 'sharePing'])->name('posts.share-ping');

// Authentication Routes
Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    // Registration
    Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');

    // Forgot Password
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:3,1')->name('password.email');

    // Reset Password
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:3,1')->name('password.store');

    // Social Authentication
    Route::get('/auth/google', [SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);
    Route::get('/auth/linkedin', [SocialAuthController::class, 'redirectToLinkedIn'])->name('auth.linkedin');
    Route::get('/auth/linkedin/callback', [SocialAuthController::class, 'handleLinkedInCallback']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Email Verification
    Route::get('/email/verify', [AuthController::class, 'showVerifyEmailForm'])->name('verification.notice');
    Route::post('/email/verification-notification', [AuthController::class, 'sendVerificationEmail'])->name('verification.send');
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    // Dashboard
    Route::get('/home', [App\Http\Controllers\DashboardController::class, 'index'])->name('home');
    Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');

    // Hackathons Dashboard
    Route::prefix('home/hackathons')->name('home.hackathons.')->group(function () {
        Route::get('/teams', [App\Http\Controllers\HackathonDashboardController::class, 'teams'])->name('teams');
        Route::get('/teams/{team}', [App\Http\Controllers\HackathonDashboardController::class, 'showTeam'])->name('teams.show');

        // Team Management
        Route::post('/teams/create', [App\Http\Controllers\HackathonDashboardController::class, 'createTeam'])->name('teams.create');
        Route::post('/teams/join', [App\Http\Controllers\HackathonDashboardController::class, 'joinTeam'])->name('teams.join');
        Route::post('/teams/leave', [App\Http\Controllers\HackathonDashboardController::class, 'leaveTeam'])->name('teams.leave');
        Route::delete('/teams/join-cancel', [App\Http\Controllers\HackathonDashboardController::class, 'cancelJoinRequest'])->name('teams.join-cancel');
        Route::delete('/teams/disband', [App\Http\Controllers\HackathonDashboardController::class, 'disbandTeam'])->name('teams.disband');
        Route::put('/teams/update', [App\Http\Controllers\HackathonDashboardController::class, 'updateTeam'])->name('teams.update');

        // Team Invitations
        Route::post('/teams/invite', [App\Http\Controllers\HackathonDashboardController::class, 'inviteMember'])->name('teams.invite');
        Route::post('/invitations/accept', [App\Http\Controllers\HackathonDashboardController::class, 'acceptInvitation'])->name('invitations.accept');
        Route::post('/invitations/reject', [App\Http\Controllers\HackathonDashboardController::class, 'rejectInvitation'])->name('invitations.reject');
        Route::delete('/invitations/delete', [App\Http\Controllers\HackathonDashboardController::class, 'deleteInvitation'])->name('invitations.delete');

        // Join Requests
        Route::post('/join-requests/accept', [App\Http\Controllers\HackathonDashboardController::class, 'acceptJoinRequest'])->name('join-requests.accept');
        Route::post('/join-requests/reject', [App\Http\Controllers\HackathonDashboardController::class, 'rejectJoinRequest'])->name('join-requests.reject');

        // Project Management
        Route::post('/teams/{team}/projects', [App\Http\Controllers\HackathonProjectController::class, 'store'])->name('projects.store');
        Route::put('/teams/projects/{project}', [App\Http\Controllers\HackathonProjectController::class, 'update'])->name('projects.update');
        Route::post('/teams/projects/{project}/files', [App\Http\Controllers\HackathonProjectController::class, 'uploadFiles'])->name('projects.upload-files');
        Route::delete('/teams/projects/files/{file}', [App\Http\Controllers\HackathonProjectController::class, 'deleteFile'])->name('projects.delete-file');
        Route::get('/teams/projects/files/{file}/download', [App\Http\Controllers\HackathonProjectController::class, 'downloadFile'])->name('projects.download-file');
    });
    Route::get('/posts/load-more', [App\Http\Controllers\DashboardController::class, 'loadMorePosts'])->name('posts.load-more');

    // Profile Management
    Route::get('/profile/complete', [ProfileController::class, 'showCompletionForm'])->name('profile.complete');
    Route::post('/profile/complete', [ProfileController::class, 'completeProfile'])->name('profile.complete.submit');
    Route::get('/profile/complete/skip', [ProfileController::class, 'skipProfile'])->name('profile.complete.skip');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');
    Route::delete('/profile/delete', [ProfileController::class, 'deleteAccount'])->name('profile.delete');
    Route::get('/api/profile/progress', [ProfileController::class, 'getCompletionProgress'])->name('profile.progress');
    Route::get('/profile/{id}', [ProfileController::class, 'show'])->name('profile.show.other');
    Route::get('/profile/{id}/posts', [ProfileController::class, 'userPosts'])->name('profile.posts');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Experience routes
    Route::post('/profile/experiences', [ProfileController::class, 'storeExperience'])->name('experience.store');
    Route::get('/profile/experiences/{id}', [ProfileController::class, 'getExperience'])->name('experience.get');
    Route::put('/profile/experiences/{id}', [ProfileController::class, 'updateExperience'])->name('experience.update');
    Route::delete('/profile/experiences/{id}', [ProfileController::class, 'deleteExperience'])->name('experience.delete');

    // Portfolio routes
    Route::post('/profile/portfolios', [ProfileController::class, 'storePortfolio'])->name('portfolio.store');
    Route::get('/profile/portfolios/{id}', [ProfileController::class, 'getPortfolio'])->name('portfolio.get');
    Route::put('/profile/portfolios/{id}', [ProfileController::class, 'updatePortfolio'])->name('portfolio.update');
    Route::delete('/profile/portfolios/{id}', [ProfileController::class, 'deletePortfolio'])->name('portfolio.delete');

    // Job Listings
    // Put specific routes BEFORE resource route to avoid conflicts
    Route::get('/jobs/my-applications', [JobListingController::class, 'myApplications'])->name('jobs.my-applications');
    Route::get('/jobs/my-applications/{application}', [JobListingController::class, 'showMyApplication'])->name('jobs.my-application.show');
    Route::resource('jobs', JobListingController::class);
    Route::post('/jobs/{job}/apply', [JobListingController::class, 'apply'])->name('jobs.apply');
    Route::patch('/jobs/{job}/close', [JobListingController::class, 'close'])->name('jobs.close');
    Route::patch('/jobs/{job}/reopen', [JobListingController::class, 'reopen'])->name('jobs.reopen');
    Route::patch('/jobs/{job}/archive', [JobListingController::class, 'archive'])->name('jobs.archive');
    Route::get('/jobs/{job}/applications', [JobListingController::class, 'applications'])->name('jobs.applications');

    // Job Application Management
    Route::patch('/jobs/applications/{application}/review', [JobListingController::class, 'reviewApplication'])->name('jobs.applications.review');
    Route::patch('/jobs/applications/{application}/accept', [JobListingController::class, 'acceptApplication'])->name('jobs.applications.accept');
    Route::patch('/jobs/applications/{application}/reject', [JobListingController::class, 'rejectApplication'])->name('jobs.applications.reject');
    Route::patch('/jobs/applications/{application}/notes', [JobListingController::class, 'updateApplicationNotes'])->name('jobs.applications.notes');

    // Events
    Route::resource('events', EventController::class);
    Route::post('/events/{event}/register', [EventController::class, 'register'])->name('events.register');
    Route::delete('/events/{event}/cancel-registration', [EventController::class, 'cancelRegistration'])->name('events.cancel-registration');
    Route::post('/events/{event}/check-in', [EventController::class, 'checkIn'])->name('events.check-in');
    Route::get('/events/{event}/registrations', [EventController::class, 'registrations'])->name('events.registrations');
    Route::get('/events/{event}/export-registrations', [EventController::class, 'exportRegistrations'])->name('events.export-registrations');

    // Solutions Page
    Route::get('/solutions', [App\Http\Controllers\SolutionsController::class, 'index'])->name('solutions.index');

    // Posts Management
    Route::resource('posts', PostController::class)->except(['index']);
    // Posts index route - redirect to home (posts are shown on dashboard)
    Route::get('/posts', function () {
        return redirect()->route('home');
    })->name('posts.index');
    Route::post('/posts/{post}/like', [PostController::class, 'toggleLike'])->name('posts.like');
    Route::post('/posts/{post}/vote', [PostController::class, 'vote'])->name('posts.vote');

    // Comments Routes
    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::get('/posts/{post}/comments', [CommentController::class, 'index'])->name('comments.index');
    Route::put('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('/comments/{comment}/approve', [CommentController::class, 'approve'])->name('comments.approve');
    Route::post('/comments/{comment}/reject', [CommentController::class, 'reject'])->name('comments.reject');

    // Hackathons
    Route::resource('hackathons', HackathonController::class);
    Route::post('/hackathons/{hackathon}/join-team', [HackathonController::class, 'joinTeam'])->name('hackathons.join-team');
    Route::delete('/hackathons/{hackathon}/leave-team', [HackathonController::class, 'leaveTeam'])->name('hackathons.leave-team');
    Route::post('/hackathons/{hackathon}/register', [HackathonController::class, 'register'])->name('hackathons.register');
    Route::get('/hackathons/{hackathon}/teams', [HackathonController::class, 'teams'])->name('hackathons.teams');
    Route::get('/hackathons/{hackathon}/participants', [HackathonController::class, 'participants'])->name('hackathons.participants');
    Route::get('/hackathons/{hackathon}/export-participants', [HackathonController::class, 'exportParticipants'])->name('hackathons.export-participants');

    // Internship Applications
    Route::get('/internships/apply', [InternshipApplicationController::class, 'create'])->name('internships.apply');
    Route::post('/internships/apply', [InternshipApplicationController::class, 'store'])->name('internships.applications.store');
    Route::get('/internships/my-applications', [\App\Http\Controllers\InternshipController::class, 'myApplications'])->name('internships.my-applications');
    Route::resource('internships', \App\Http\Controllers\InternshipController::class)->only(['index', 'create', 'store', 'show']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
    Route::get('/notifications/get', [NotificationController::class, 'getNotifications'])->name('notifications.get');
    Route::post('/notifications/view-all', [NotificationController::class, 'markAllAsViewed'])->name('notifications.view-all');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::delete('/notifications', [NotificationController::class, 'clear'])->name('notifications.clear');
    Route::post('/notifications/preferences', [NotificationController::class, 'updatePreferences'])->name('notifications.preferences');
    Route::post('/notifications/{notificationId}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::delete('/notifications/{notificationId}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Search
    Route::get('/search', [SearchController::class, 'searchResults'])->name('search.results');
    Route::get('/search/live', [SearchController::class, 'liveSearch'])->name('search.live');
    Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');
    Route::get('/search/advanced', [SearchController::class, 'advanced'])->name('search.advanced');

    // API Routes
    Route::get('/api/users/search', [\App\Http\Controllers\Api\UserSearchController::class, 'search'])->name('api.users.search');

    // Client Conversion Request
    Route::middleware(['auth'])->group(function () {
        Route::get('/client/request', [App\Http\Controllers\ClientConversionRequestController::class, 'create'])->name('client.request');
        Route::post('/client/request', [App\Http\Controllers\ClientConversionRequestController::class, 'store'])->name('client.request.store');
        Route::get('/client/request/{clientConversionRequest}', [App\Http\Controllers\ClientConversionRequestController::class, 'show'])->name('client.conversion-request.show');
        Route::get('/client/request/{clientConversionRequest}/edit', [App\Http\Controllers\ClientConversionRequestController::class, 'edit'])->name('client.conversion-request.edit');
        Route::put('/client/request/{clientConversionRequest}', [App\Http\Controllers\ClientConversionRequestController::class, 'update'])->name('client.conversion-request.update');
        Route::delete('/client/request/{clientConversionRequest}', [App\Http\Controllers\ClientConversionRequestController::class, 'destroy'])->name('client.conversion-request.destroy');
    });

    // Client Dashboard (for users with client role)
    Route::middleware(['auth', 'role:client,superadmin,admin'])->group(function () {
        Route::get('/client/dashboard', function () {
            return view('client.dashboard');
        })->name('client.dashboard');
    });

    // Admin Routes (restricted to admin/superadmin)
    Route::middleware(['auth', 'role:superadmin,admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/content', [AdminController::class, 'content'])->name('content');
        Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
        // Route::resource('event-categories', App\Http\Controllers\Admin\EventCategoryController::class);
    });
});
