<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewNotificationEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notification;

    // Hapus type-hint 'Notification' di depan parameter $notification
    public function __construct($notification)
    {
        $this->notification = $notification;
    }

    /**
     * Channel publik tempat admin akan mendengarkan notifikasi real-time
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('admin-notifications'),
        ];
    }

    /**
     * Nama event WebSocket yang akan ditangkap oleh frontend (React/Vue/Postman)
     */
    public function broadcastAs(): string
    {
        return 'notification.created';
    }
}
