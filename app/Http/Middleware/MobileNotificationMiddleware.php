<?php

namespace App\Http\Middleware;

use App\Services\MobileNotificationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MobileNotificationMiddleware
{
    protected MobileNotificationService $mobileNotificationService;

    public function __construct(MobileNotificationService $mobileNotificationService)
    {
        $this->mobileNotificationService = $mobileNotificationService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Add notification badge count to response headers for mobile apps
        if ($request->user() && $request->hasHeader('X-Mobile-App')) {
            $unreadCount = $request->user()->unreadNotifications()->count();
            $response->headers->set('X-Notification-Count', $unreadCount);
            
            // Check if FCM token needs refresh
            if ($request->user()->fcm_token && $request->hasHeader('X-FCM-Token')) {
                $currentToken = $request->header('X-FCM-Token');
                
                if ($currentToken !== $request->user()->fcm_token) {
                    // Token has changed, update it
                    $this->mobileNotificationService->registerDevice($request->user(), [
                        'fcm_token' => $currentToken,
                        'device_type' => $request->header('X-Device-Type'),
                        'device_info' => [
                            'app_version' => $request->header('X-App-Version'),
                            'os_version' => $request->header('X-OS-Version'),
                        ],
                    ]);
                }
            }
        }

        return $response;
    }
}
