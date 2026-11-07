<?php

namespace App\Actions\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AttemptAdminLogin
{
    public function __invoke(string $password): bool
    {
        $admin = User::query()->orderBy('id')->first();

        if ($admin === null || ! Hash::check($password, $admin->password)) {
            return false;
        }

        Auth::guard('web')->login($admin);

        return true;
    }
}
