<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use App\Notifications\NewOrderNotification;

class CartService
{
    public function __construct(private readonly DatabaseManager $db)
    {
    }

    public function getOrCreateCartForUser(User $user): Cart
    {
        return Cart::query()
            ->with(['items.product'])
            ->firstOrCreate(['user_id' => $user->id]);
    }

    public function itemsForUser(User $user): Collection
    {
        return $this->getOrCreateCartForUser($user)
            ->items
            ->load('product');
    }

    public function addItem(User $user, int $productId, int $quantity): Cart
    {
        return $this->db->transaction(function () use ($user, $productId, $quantity): Cart {
            $cart = $this->getOrCreateCartForUser($user);

            /** @var Product $product */
            $product = Product::query()->findOrFail($productId);

            $existing = $cart->items
                ->firstWhere('product_id', $product->id);

            $newQuantity = $quantity;

            if ($existing instanceof CartItem) {
                $newQuantity = $existing->quantity + $quantity;
            }

            if ($newQuantity > $product->stock_quantity) {
                throw new RuntimeException('Requested quantity exceeds available stock.');
            }

            CartItem::query()->updateOrCreate(
                ['cart_id' => $cart->id, 'product_id' => $product->id],
                [
                    'quantity' => $newQuantity,
                    'unit_price' => $product->price,
                ],
            );

            return $cart->fresh(['items.product']);
        });
    }

    public function updateItemQuantity(User $user, int $cartItemId, int $quantity): Cart
    {
        return $this->db->transaction(function () use ($user, $cartItemId, $quantity): Cart {
            $cart = $this->getOrCreateCartForUser($user);

            /** @var CartItem $item */
            $item = $cart->items()->whereKey($cartItemId)->with('product')->firstOrFail();

            if ($quantity < 1) {
                $item->delete();

                return $cart->fresh(['items.product']);
            }

            $product = $item->product;

            if ($quantity > $product->stock_quantity) {
                throw new RuntimeException('Requested quantity exceeds available stock.');
            }

            $item->quantity = $quantity;
            $item->save();

            return $cart->fresh(['items.product']);
        });
    }

    public function removeItem(User $user, int $cartItemId): Cart
    {
        return $this->db->transaction(function () use ($user, $cartItemId): Cart {
            $cart = $this->getOrCreateCartForUser($user);

            $cart->items()->whereKey($cartItemId)->delete();

            return $cart->fresh(['items.product']);
        });
    }

    public function checkout(User $user): Order
    {
        return $this->db->transaction(function () use ($user): Order {
            $cart = Cart::query()
                ->where('user_id', $user->id)
                ->with(['items.product'])
                ->firstOrFail();

            if ($cart->items->isEmpty()) {
                throw new RuntimeException('Cart is empty.');
            }

            $total = $cart->items->reduce(
                fn (string $carry, CartItem $item): string => (string) ((float) $carry + ((float) $item->unit_price * $item->quantity)),
                '0',
            );

            $order = Order::create([
                'user_id' => $user->id,
                'total_amount' => $total,
                'status' => OrderStatus::Pending,
            ]);

            foreach ($cart->items as $item) {
                $product = $item->product;

                if ($item->quantity > $product->stock_quantity) {
                    throw new RuntimeException('Insufficient stock for product.');
                }

                $product->stock_quantity -= $item->quantity;
                $product->save();

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => (string) ((float) $item->unit_price * $item->quantity),
                ]);
            }

            $cart->items()->delete();

            $admins = User::query()->where('role', UserRole::Admin)->get();

            if ($admins->isNotEmpty()) {
                Notification::send($admins, new NewOrderNotification($order->fresh(['items.product', 'user'])));
            }

            return $order->fresh(['items.product']);
        });
    }
}
