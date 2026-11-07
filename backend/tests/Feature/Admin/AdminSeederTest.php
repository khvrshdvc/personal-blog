<?php

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Support\Facades\Hash;

it('creates the single admin from env config', function () {
    config(['admin.email' => 'owner@example.com', 'admin.password' => 'maxfiy-parol']);

    $this->seed(AdminSeeder::class);
    $this->seed(AdminSeeder::class);

    expect(User::count())->toBe(1);
    expect(Hash::check('maxfiy-parol', User::first()->password))->toBeTrue();
});

it('skips creation when env values are missing', function () {
    config(['admin.email' => null, 'admin.password' => null]);

    $this->seed(AdminSeeder::class);

    expect(User::count())->toBe(0);
});
