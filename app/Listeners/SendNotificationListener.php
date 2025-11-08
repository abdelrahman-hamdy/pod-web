<?php

namespace App\Listeners;

use App\Events\NotificationEvent;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendNotificationListener implements ShouldQueue
{
    use InteractsWithQueue;

    protected NotificationService $notificationService;

    /**
     * Create the event listener.
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(NotificationEvent $event): void
    {
        // Send notification through the notification service
        $this->notificationService->send(
            $event->user,
            $event->type,
            $event->data,
            $event->channels
        );
    }

    /**
     * Handle a job failure.
     */
    public function failed(NotificationEvent $event, \Throwable $exception): void
    {
        \Log::error('Failed to send notification', [
            'user_id' => $event->user->id,
            'type' => $event->type->value,
            'error' => $exception->getMessage(),
        ]);
    }
}
