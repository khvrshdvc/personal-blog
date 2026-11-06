<?php

namespace App\Actions\Admin;

use Illuminate\Support\Facades\Auth;

class AuthenticateAdmin
{
    public function __invoke(string $email, string $password, bool $remember): bool
    {
        if (! Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
            return false;
        }

        request()->session()->regenerate();

        return true;
    }
}
