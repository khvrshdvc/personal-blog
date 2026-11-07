<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('admin.email');
        $password = config('admin.password');

        if (! $email || ! $password) {
            $this->command?->warn('ADMIN_EMAIL va ADMIN_PASSWORD .env da to\'ldirilmagan.');

            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            ['name' => 'Utkirbek', 'password' => Hash::make($password)],
        );
    }
}
