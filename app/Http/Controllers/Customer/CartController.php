<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CartAddItemRequest;
use App\Http\Requests\Customer\CartUpdateItemRequest;
use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    public function index(): Response
    {
        $user = Auth::user();

        $cart = $this->cartService->getOrCreateCartForUser($user);

        $items = $cart->items->map(fn (CartItem $item): array => [
            'id' => $item->id,
            'quantity' => $item->quantity,
            'unit_price' => (string) $item->unit_price,
            'subtotal' => (string) ((float) $item->unit_price * $item->quantity),
            'product' => [
                'id' => $item->product?->id,
                'name' => $item->product?->name,
                'price' => (string) $item->product?->price,
                'stock_quantity' => $item->product?->stock_quantity,
                'image_path' => $item->product?->image_path,
            ],
        ]);

        $total = $items->reduce(
            fn (string $carry, array $item): string => (string) ((float) $carry + (float) $item['subtotal']),
            '0',
        );

        return Inertia::render('cart/Index', [
            'cart' => [
                'items' => $items,
                'total' => $total,
            ],
        ]);
    }

    public function store(CartAddItemRequest $request): RedirectResponse
    {
        $user = $request->user();

        try {
            $this->cartService->addItem($user, (int) $request->integer('product_id'), (int) $request->integer('quantity'));
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Item added to cart.');
    }

    public function update(CartUpdateItemRequest $request, int $item): RedirectResponse
    {
        $user = $request->user();

        try {
            $this->cartService->updateItemQuantity($user, $item, (int) $request->integer('quantity'));
            return back()->with('success', "Item updated successfully" );
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Cart updated.');
    }

    public function destroy(int $item): RedirectResponse
    {
        $user = Auth::user();

        $this->cartService->removeItem($user, $item);

        return back()->with('success', 'Item removed from cart.');
    }
}
