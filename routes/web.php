<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])
    ->group(function (): void {
        Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])
            ->name('notifications.markAllRead');
        Route::post('/notifications/{notification}/mark-read', [NotificationController::class, 'markRead'])
            ->name('notifications.markRead');
    });

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
require __DIR__.'/customer.php';
