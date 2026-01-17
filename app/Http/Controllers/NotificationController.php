<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function markAllRead(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user !== null) {
            $user->unreadNotifications->markAsRead();
        }

        return back();
    }

    public function markRead(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        $user = $request->user();

        if ($user === null || $notification->notifiable_id !== $user->getKey() || $notification->notifiable_type !== $user::class) {
            abort(404);
        }

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        return back();
    }


}
