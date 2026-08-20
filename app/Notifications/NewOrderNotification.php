<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification
{
    use Queueable;

    protected $order;

    /**
     * Create a new notification instance.
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Menggunakan channel 'database' agar tersimpan di tabel notifications
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // Data ini yang akan disimpan ke kolom 'data' di tabel notifications
        return [
            'order_id'     => $this->order->id ?? null,
            'order_number' => $this->order->order_number ?? null,
            'message'      => 'Pesanan baru masuk #' . ($this->order->order_number ?? 'Unknown'),
        ];
    }
}
