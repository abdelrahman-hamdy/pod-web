<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\HackathonController;
use App\Http\Controllers\Api\InternshipController;
use App\Http\Controllers\Api\JobListingController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// API Version 1
Route::prefix('v1')->group(function () {

    // Public Routes (No Authentication Required)
    Route::group(['middleware' => ['throttle:20,1']], function () {
        // Authentication - More restrictive rate limiting
        Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
        Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
        Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:3,1');
        Route::post('/auth/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:3,1');

        // Public Resources (Limited) - with optional auth for personalized features
        Route::get('/posts', [PostController::class, 'index'])->middleware('auth:sanctum');
        Route::get('/posts/{post}', [PostController::class, 'show'])->middleware('auth:sanctum');
        Route::get('/events', [EventController::class, 'index']);
        Route::get('/events/{event}', [EventController::class, 'show']);
        Route::get('/jobs', [JobListingController::class, 'index']);
        Route::get('/jobs/{job}', [JobListingController::class, 'show'])->where('job', '[0-9]+');
        Route::get('/hackathons', [HackathonController::class, 'index']);
        Route::get('/hackathons/{hackathon}', [HackathonController::class, 'show']);
        Route::get('/internships', [InternshipController::class, 'index']);
        Route::get('/internships/{internship}', [InternshipController::class, 'show'])->where('internship', '[0-9]+');
    });

    // Authenticated Routes (Sanctum Required)
    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {

        // Authentication
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::post('/auth/refresh', [AuthController::class, 'refresh']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::get('/auth/verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
            ->middleware(['signed'])
            ->name('api.verification.verify');
        Route::post('/auth/resend-verification', [AuthController::class, 'resendVerification']);

        // User Management
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/search', [UserController::class, 'search']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::get('/users/{user}/posts', [UserController::class, 'posts']);
        Route::get('/users/{user}/experiences', [UserController::class, 'experiences']);
        Route::get('/users/{user}/portfolios', [UserController::class, 'portfolios']);

        // Profile Management
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::get('/profile/progress', [ProfileController::class, 'getProgress']);
        Route::post('/profile/complete', [ProfileController::class, 'complete']);
        Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar']);

        // Profile Experiences
        Route::get('/profile/experiences/{experience}', [ProfileController::class, 'getExperience']);
        Route::post('/profile/experiences', [ProfileController::class, 'storeExperience']);
        Route::put('/profile/experiences/{experience}', [ProfileController::class, 'updateExperience']);
        Route::delete('/profile/experiences/{experience}', [ProfileController::class, 'deleteExperience']);

        // Profile Portfolios
        Route::get('/profile/portfolios/{portfolio}', [ProfileController::class, 'getPortfolio']);
        Route::post('/profile/portfolios', [ProfileController::class, 'storePortfolio']);
        Route::put('/profile/portfolios/{portfolio}', [ProfileController::class, 'updatePortfolio']);
        Route::delete('/profile/portfolios/{portfolio}', [ProfileController::class, 'deletePortfolio']);

        // Business Account Requests
        Route::get('/profile/business-request/status', [App\Http\Controllers\Api\ClientConversionController::class, 'getStatus']);
        Route::post('/profile/business-request', [App\Http\Controllers\Api\ClientConversionController::class, 'submit']);

        // Posts
        Route::post('/posts', [PostController::class, 'store']);
        Route::put('/posts/{post}', [PostController::class, 'update']);
        Route::delete('/posts/{post}', [PostController::class, 'destroy']);
        Route::post('/posts/{post}/like', [PostController::class, 'toggleLike']);
        Route::post('/posts/{post}/share', [PostController::class, 'share']);
        Route::post('/posts/{post}/vote', [PostController::class, 'vote']);

        // Comments
        Route::get('/posts/{post}/comments', [CommentController::class, 'index']);
        Route::post('/posts/{post}/comments', [CommentController::class, 'store']);
        Route::put('/comments/{comment}', [CommentController::class, 'update']);
        Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

        // Events
        Route::post('/events', [EventController::class, 'store']);
        Route::put('/events/{event}', [EventController::class, 'update']);
        Route::delete('/events/{event}', [EventController::class, 'destroy']);
        Route::post('/events/{event}/register', [EventController::class, 'register']);
        Route::delete('/events/{event}/register', [EventController::class, 'cancelRegistration']);
        Route::post('/events/{event}/check-in', [EventController::class, 'checkIn']);
        Route::get('/events/{event}/registrations', [EventController::class, 'registrations'])
            ->middleware('role:admin,superadmin,client');

        // Job Listings
        Route::post('/jobs', [JobListingController::class, 'store']);
        Route::get('/jobs/my-applications', [JobListingController::class, 'myApplications']);
        Route::put('/jobs/{job}', [JobListingController::class, 'update'])->where('job', '[0-9]+');
        Route::delete('/jobs/{job}', [JobListingController::class, 'destroy'])->where('job', '[0-9]+');
        Route::post('/jobs/{job}/apply', [JobListingController::class, 'apply'])->where('job', '[0-9]+');
        Route::patch('/jobs/{job}/close', [JobListingController::class, 'close'])->where('job', '[0-9]+');
        Route::patch('/jobs/{job}/reopen', [JobListingController::class, 'reopen'])->where('job', '[0-9]+');
        Route::patch('/jobs/{job}/archive', [JobListingController::class, 'archive'])->where('job', '[0-9]+');
        Route::get('/jobs/{job}/applications', [JobListingController::class, 'applications'])
            ->where('job', '[0-9]+')
            ->middleware('role:admin,superadmin,client');
        Route::patch('/jobs/applications/{application}/review', [JobListingController::class, 'reviewApplication'])
            ->middleware('role:admin,superadmin,client');
        Route::patch('/jobs/applications/{application}/accept', [JobListingController::class, 'acceptApplication'])
            ->middleware('role:admin,superadmin,client');
        Route::patch('/jobs/applications/{application}/reject', [JobListingController::class, 'rejectApplication'])
            ->middleware('role:admin,superadmin,client');
        Route::patch('/jobs/applications/{application}/notes', [JobListingController::class, 'updateApplicationNotes'])
            ->middleware('role:admin,superadmin,client');

        // Hackathons
        Route::post('/hackathons', [HackathonController::class, 'store']);
        Route::put('/hackathons/{hackathon}', [HackathonController::class, 'update']);
        Route::delete('/hackathons/{hackathon}', [HackathonController::class, 'destroy']);
        Route::post('/hackathons/{hackathon}/register', [HackathonController::class, 'register']);
        Route::get('/hackathons/{hackathon}/teams', [HackathonController::class, 'teams']);
        Route::post('/hackathons/{hackathon}/join-team', [HackathonController::class, 'joinTeam']);
        Route::delete('/hackathons/{hackathon}/leave-team', [HackathonController::class, 'leaveTeam']);

        // Hackathon Teams
        Route::get('/hackathons/teams', [HackathonController::class, 'myTeams']);
        Route::post('/hackathons/teams', [HackathonController::class, 'createTeam']);
        Route::put('/hackathons/teams/{team}', [HackathonController::class, 'updateTeam']);
        Route::delete('/hackathons/teams/{team}', [HackathonController::class, 'deleteTeam']);
        Route::post('/hackathons/teams/{team}/invite', [HackathonController::class, 'inviteMember']);
        Route::post('/hackathons/teams/{team}/join-request', [HackathonController::class, 'requestToJoin']);
        Route::post('/hackathons/invitations/{invitation}/accept', [HackathonController::class, 'acceptInvitation']);
        Route::post('/hackathons/invitations/{invitation}/reject', [HackathonController::class, 'rejectInvitation']);
        Route::post('/hackathons/join-requests/{request}/accept', [HackathonController::class, 'acceptJoinRequest']);
        Route::post('/hackathons/join-requests/{request}/reject', [HackathonController::class, 'rejectJoinRequest']);

        // Internships
        Route::post('/internships/apply', [InternshipController::class, 'apply']);
        Route::get('/internships/my-applications', [InternshipController::class, 'myApplications']);

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::patch('/notifications/{notificationId}/read', [NotificationController::class, 'markAsRead']);
        Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/notifications/{notificationId}', [NotificationController::class, 'destroy']);
        Route::delete('/notifications', [NotificationController::class, 'clear']);
        Route::post('/notifications/preferences', [NotificationController::class, 'updatePreferences']);
        Route::post('/notifications/fcm-token', [NotificationController::class, 'registerFcmToken']);
        Route::delete('/notifications/fcm-token', [NotificationController::class, 'removeFcmToken']);

        // Search
        Route::get('/search', [SearchController::class, 'index']);
        Route::get('/search/posts', [SearchController::class, 'posts']);
        Route::get('/search/events', [SearchController::class, 'events']);
        Route::get('/search/jobs', [SearchController::class, 'jobs']);
        Route::get('/search/hackathons', [SearchController::class, 'hackathons']);
        Route::get('/search/users', [SearchController::class, 'users']);

        // Real-time Broadcasting (for mobile apps)
        Route::post('/broadcasting/auth', [\App\Http\Controllers\Chatify\MessagesController::class, 'pusherAuth'])
            ->name('api.broadcasting.auth');
    });

    // Strict Rate Limiting Routes (Authentication)
    Route::middleware(['throttle:10,1'])->group(function () {
        // These are handled above, but grouped for clarity
    });
});
