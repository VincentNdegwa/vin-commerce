<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Enums\UserRole;

class NewOrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Order $order)
    {
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        $order = $this->order->loadMissing(['user']);

        $isAdmin = method_exists($notifiable, 'getAttribute')
            && $notifiable->role === UserRole::Admin;

        $actionUrl = $isAdmin
            ? route('admin.orders.show', ['order' => $order->id], false)
            : route('orders.show', ['order' => $order->id], false);

        return [
            'title' => 'New order #' . $order->id,
            'message' => 'Total: ' . (string) $order->total_amount . ' • Status: ' . $order->status->value,
            'order_id' => $order->id,
            'total_amount' => (string) $order->total_amount,
            'status' => $order->status->value,
            'customer_name' => $order->user?->name,
            'placed_at' => $order->created_at?->toIso8601String(),
            'action_url' => $actionUrl,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order->loadMissing(['user']);

        $isAdmin = method_exists($notifiable, 'getAttribute')
            && $notifiable->role === UserRole::Admin;

        $actionUrl = $isAdmin
            ? route('admin.orders.show', ['order' => $order->id])
            : route('orders.show', ['order' => $order->id]);

        $message = (new MailMessage())
            ->subject('New order #' . $order->id)
            ->line('A new order has been placed with total ' . (string) $order->total_amount . '.')
            ->action('View order', $actionUrl);

        if ($order->user !== null) {
            $message->line('Customer: ' . $order->user->name . ' (' . $order->user->email . ')');
        }

        return $message;
    }
}
