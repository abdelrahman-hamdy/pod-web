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
        // Disable schema dumping in production
        if ($this->app->isProduction()) {
            Schema::defaultStringLength(191);
            $this->app->make('db.schema')->dump(
                $this->app->make('db')->connection(),
                new \Illuminate\Database\Schema\Grammars\NullGrammar,
                $this->app->make('files')->put('/dev/null', '')
            );
        }

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
