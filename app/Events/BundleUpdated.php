<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BundleUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $bundle;

    public function __construct($bundle)
    {
        $this->bundle = $bundle;
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
     * Nama event yang diterima frontend.
     */
    public function broadcastAs(): string
    {
        return 'bundle.updated';
    }

    /**
     * Data yang dikirim ke frontend.
     */
    public function broadcastWith(): array
    {
        return [
            'bundle' => $this->bundle,
        ];
    }
}