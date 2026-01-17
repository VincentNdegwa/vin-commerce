<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\UserRole;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Product $product)
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
        $product = $this->product->loadMissing('creator');

        $isAdmin = method_exists($notifiable, 'getAttribute')
            && $notifiable->role === UserRole::Admin;

        $actionUrl = $isAdmin
            ? route('admin.products.edit', ['product' => $product->id], false)
            : route('admin.products.edit', ['product' => $product->id], false);

        return [
            'title' => 'Low stock: ' . $product->name,
            'message' => 'Remaining stock: ' . $product->stock_quantity,
            'product_id' => $product->id,
            'name' => $product->name,
            'stock_quantity' => $product->stock_quantity,
            'creator_name' => $product->creator?->name,
            'creator_email' => $product->creator?->email,
            'action_url' => $actionUrl,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $product = $this->product->loadMissing('creator');

        $actionUrl = route('admin.products.edit', ['product' => $product->id]);

        return (new MailMessage())
            ->subject('Low stock alert: ' . $product->name)
            ->line('Stock for product "' . $product->name . '" is low: ' . $product->stock_quantity . ' remaining.')
            ->action('View product', $actionUrl);
    }
}
