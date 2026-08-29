<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MenuUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $menu;

    public function __construct($menu)
    {
        $this->menu = $menu;
    }

    /**
     * Channel untuk customer.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('customer-menu'),
        ];
    }

    /**
     * Nama event yang diterima FE.
     */
    public function broadcastAs(): string
    {
        return 'menu.updated';
    }

    /**
     * Data yang dikirim ke FE.
     */
    public function broadcastWith(): array
    {
        return [
            'menu' => $this->menu,
        ];
    }
}
