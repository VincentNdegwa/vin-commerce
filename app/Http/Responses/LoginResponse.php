<?php

declare(strict_types=1);

namespace App\Http\Responses;

use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        $defaultHome = config('fortify.home');

        $home = $user !== null && $user->role === UserRole::Customer
            ? route('shop.index')
            : $defaultHome;

        return redirect()->intended($home);
    }
}
