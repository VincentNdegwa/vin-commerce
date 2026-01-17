<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CheckoutRequest;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;

class CheckoutController extends Controller
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        try {
            $user = $request->user();
    
            $order = $this->cartService->checkout($user);
    
            return redirect()->route('orders.show', ['order' => $order->id])->with('success', 'Checkout created successfully.');
            
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }
}
