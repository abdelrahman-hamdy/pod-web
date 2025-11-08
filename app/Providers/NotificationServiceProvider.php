<?php

namespace App\Providers;

use App\Services\FirebaseNotificationService;
use App\Services\MobileNotificationService;
use App\Services\NotificationService;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register Firebase service as singleton
        $this->app->singleton(FirebaseNotificationService::class, function ($app) {
            return new FirebaseNotificationService();
        });

        // Register Notification service as singleton
        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService($app->make(FirebaseNotificationService::class));
        });

        // Register Mobile Notification service
        $this->app->singleton(MobileNotificationService::class, function ($app) {
            $firebaseService = $app->make(FirebaseNotificationService::class);
            $notificationService = $app->make(NotificationService::class);
            
            $mobileService = new MobileNotificationService($firebaseService, $notificationService);
            
            // Inject mobile service back into notification service
            $notificationService->setMobileService($mobileService);
            
            return $mobileService;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
