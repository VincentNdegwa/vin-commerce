<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService)
    {
    }

    public function index(Request $request): Response
    {
        $user = $request->user();

        $orders = $this->orderService->listForCustomer($user);

        return Inertia::render('orders/Index', [
            'orders' => $orders,
        ]);
    }

    public function show(Request $request, Order $order): Response
    {
        $user = $request->user();

        try {
            $payload = $this->orderService->detailForCustomer($user, $order);
        } catch (RuntimeException $exception) {
            abort(404);
        }

        return Inertia::render('orders/Show', [
            'order' => $payload,
        ]);
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $user = $request->user();

        try {
            $this->orderService->cancelOrderAsCustomer($user, $order);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Order cancelled.');
    }
}
