<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileCompleted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Check if column exists to prevent errors during migration deployment
        $hasOnboardingColumn = Schema::hasColumn('users', 'profile_onboarding_seen');

        // If user is authenticated and hasn't seen the profile onboarding yet
        if ($user && $hasOnboardingColumn && ! $user->profile_onboarding_seen) {
            // Allow access to profile completion route, skip route, and logout route
            if (! $request->routeIs('profile.complete')
                && ! $request->routeIs('profile.complete.submit')
                && ! $request->routeIs('profile.complete.skip')
                && ! $request->routeIs('logout')) {

                return redirect()->route('profile.complete');
            }
        }

        return $next($request);
    }
}
