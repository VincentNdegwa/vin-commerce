<?php

namespace App\Http\Middleware;

use App\Models\Cart;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        $notifications = null;
        $cartItemCount = null;

        if ($user !== null) {
            $unread = $user->unreadNotifications()
                ->orderByDesc('created_at')
                ->take(10)
                ->get()
                ->map(fn ($notification) => [
                    'id' => $notification->id,
                    'data' => $notification->data,
                    'created_at' => $notification->created_at,
                ]);

            $notifications = [
                'unread_count' => $user->unreadNotifications()->count(),
                'items' => $unread,
            ];
        }

        if ($user !== null) {
            $cart = Cart::query()
                ->where('user_id', $user->id)
                ->withCount('items')
                ->first();

            $cartItemCount = $cart?->items_count ?? 0;
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'info' => $request->session()->get('info'),
            ],
            'cart' => [
                'item_count' => $cartItemCount,
            ],
            'notifications' => $notifications,
        ];
    }
}
