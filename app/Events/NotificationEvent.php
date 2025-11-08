<?php

namespace App\Events;

use App\Models\User;
use App\NotificationType;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public NotificationType $type;
    public array $data;
    public array $channels;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, NotificationType $type, array $data, array $channels = ['database', 'push'])
    {
        $this->user = $user;
        $this->type = $type;
        $this->data = $data;
        $this->channels = $channels;
    }
}
