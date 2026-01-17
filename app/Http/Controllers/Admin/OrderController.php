<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
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

        $orders = $this->orderService->listForAdmin($user);

        return Inertia::render('admin/orders/Index', [
            'orders' => $orders,
        ]);
    }

    public function show(Request $request, Order $order): Response
    {
        $user = $request->user();

        $payload = $this->orderService->detailForAdmin($user, $order);

        return Inertia::render('admin/orders/Show', [
            'order' => $payload,
        ]);
    }

    public function complete(Request $request, Order $order): RedirectResponse
    {
        $user = $request->user();

        try {
            $this->orderService->completeOrder($user, $order);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Order marked as completed.');
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $user = $request->user();

        try {
            $this->orderService->cancelOrderAsAdmin($user, $order);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Order cancelled.');
    }
}
