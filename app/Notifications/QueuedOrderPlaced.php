<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Order confirmation (mail + database) dispatched to the queue by
 * the checkout service. SMS/WhatsApp go through dedicated queued jobs.
 */
class QueuedOrderPlaced extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $orderNo, public string $total)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Order {$this->orderNo} placed — ChinaBay")
            ->line("Thank you! Your order {$this->orderNo} ({$this->total}) is placed.")
            ->action('Track your order', url('/track-order'))
            ->line('Pay cash on delivery. We will confirm by phone shortly.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order_placed',
            'order_no' => $this->orderNo,
            'total' => $this->total,
        ];
    }
}
