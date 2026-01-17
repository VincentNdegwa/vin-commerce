<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Order $order, private readonly User $actor)
    {
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
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
            'title' => 'Order #' . $order->id . ' cancelled',
            'message' => 'Cancelled by ' . $this->actor->name . ' • Total: ' . (string) $order->total_amount,
            'order_id' => $order->id,
            'total_amount' => (string) $order->total_amount,
            'status' => $order->status->value,
            'customer_name' => $order->user?->name,
            'cancelled_by' => $this->actor->name,
            'cancelled_by_id' => $this->actor->id,
            'cancelled_at' => now()->toIso8601String(),
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
            ->subject('Order #' . $order->id . ' was cancelled')
            ->line('Order #' . $order->id . ' with total ' . (string) $order->total_amount . ' has been cancelled.')
            ->line('Cancelled by: ' . $this->actor->name)
            ->action('View order', $actionUrl);

        if ($order->user !== null) {
            $message->line('Customer: ' . $order->user->name . ' (' . $order->user->email . ')');
        }

        return $message;
    }
}
