<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderCancelledNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use RuntimeException;

class OrderService
{

    public function listForAdmin(User $user): array
    {
        $orders = Order::query()
            ->with('user')
            ->orderByDesc('created_at')
            ->get();

        return $orders
            ->map(fn (Order $order): array => [
                'id' => $order->id,
                'total_amount' => (string) $order->total_amount,
                'status' => $order->status->value,
                'customer_name' => $order->user?->name,
                'created_at' => $order->created_at?->toIso8601String(),
            ])
            ->all();
    }


    public function detailForAdmin(User $user, Order $order): array
    {
        $order->load(['user', 'items.product']);

        return [
            'id' => $order->id,
            'total_amount' => (string) $order->total_amount,
            'status' => $order->status->value,
            'customer' => [
                'id' => $order->user?->id,
                'name' => $order->user?->name,
                'email' => $order->user?->email,
            ],
            'created_at' => $order->created_at?->toIso8601String(),
            'items' => $order->items->map(fn ($item): array => [
                'id' => $item->id,
                'product_id' => $item->product?->id,
                'product_name' => $item->product?->name,
                'quantity' => $item->quantity,
                'unit_price' => (string) $item->unit_price,
                'subtotal' => (string) $item->subtotal,
            ]),
        ];
    }


    public function listForCustomer(User $user): array
    {
        $orders = Order::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return $orders
            ->map(fn (Order $order): array => [
                'id' => $order->id,
                'total_amount' => (string) $order->total_amount,
                'status' => $order->status->value,
                'created_at' => $order->created_at?->toIso8601String(),
            ])
            ->all();
    }


    public function detailForCustomer(User $user, Order $order): array
    {
        if ($order->user_id !== $user->id) {
            throw new RuntimeException('You are not allowed to view this order.');
        }

        $order->load(['items.product']);

        return [
            'id' => $order->id,
            'total_amount' => (string) $order->total_amount,
            'status' => $order->status->value,
            'created_at' => $order->created_at?->toIso8601String(),
            'items' => $order->items->map(fn ($item): array => [
                'id' => $item->id,
                'product_id' => $item->product?->id,
                'product_name' => $item->product?->name,
                'quantity' => $item->quantity,
                'unit_price' => (string) $item->unit_price,
                'subtotal' => (string) $item->subtotal,
            ]),
        ];
    }

    public function completeOrder(User $actor, Order $order): void
    {
        if ($order->status !== OrderStatus::Pending) {
            throw new RuntimeException('Only pending orders can be completed.');
        }

        $order->status = OrderStatus::Completed;
        $order->save();
    }


    public function cancelOrderAsAdmin(User $actor, Order $order): void
    {
        if ($order->status !== OrderStatus::Pending) {
            throw new RuntimeException('Only pending orders can be cancelled.');
        }

        DB::transaction(function () use ($order): void {
            $this->restoreStockForOrder($order);

            $order->status = OrderStatus::Cancelled;
            $order->save();
        });

        $order->loadMissing('user');

        if ($order->user instanceof User) {
            $order->user->notify(new OrderCancelledNotification($order, $actor));
        }
    }


    public function cancelOrderAsCustomer(User $actor, Order $order): void
    {
        if ($order->user_id !== $actor->id) {
            throw new RuntimeException('You are not allowed to cancel this order.');
        }

        if ($order->status !== OrderStatus::Pending) {
            throw new RuntimeException('Only pending orders can be cancelled.');
        }

        DB::transaction(function () use ($order): void {
            $this->restoreStockForOrder($order);

            $order->status = OrderStatus::Cancelled;
            $order->save();
        });

        $order->loadMissing('user');

        $admins = User::query()->where('role', UserRole::Admin)->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new OrderCancelledNotification($order, $actor));
        }
    }

    private function restoreStockForOrder(Order $order): void
    {
        $order->loadMissing('items.product');

        foreach ($order->items as $item) {
            $product = $item->product;

            if ($product === null) {
                continue;
            }

            $product->stock_quantity += $item->quantity;
            $product->save();
        }
    }
}
