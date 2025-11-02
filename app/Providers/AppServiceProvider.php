<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\LinkedIn\Provider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind our custom ChatifyMessenger to override the vendor one
        $this->app->singleton('ChatifyMessenger', function ($app) {
            return new \App\Services\ChatifyMessenger;
        });

        // Also bind to 'chatify' for compatibility
        $this->app->singleton('chatify', function ($app) {
            return new \App\Services\ChatifyMessenger;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set default timezone for Carbon globally
        Carbon::setLocale(config('app.locale'));
        date_default_timezone_set(config('app.timezone'));

        // Configure LinkedIn Socialite Provider
        Socialite::extend('linkedin', function ($app) {
            $config = $app['config']['services.linkedin'];

            return Socialite::buildProvider(Provider::class, $config);
        });
    }
}
