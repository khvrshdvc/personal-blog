<?php

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    RateLimiter::clear('admin-login');
    $this->admin = User::factory()->create(['password' => 'sir-parol-123']);
    $this->withHeaders(['Origin' => 'http://localhost:3000']);
});

it('logs the admin in with the correct password', function () {
    $this->postJson('/api/admin/login', ['password' => 'sir-parol-123'])
        ->assertNoContent();

    $this->getJson('/api/admin/me')
        ->assertOk()
        ->assertExactJson(['name' => $this->admin->name]);
});

it('rejects a wrong password with 422', function () {
    $this->postJson('/api/admin/login', ['password' => 'notogri'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('password');

    $this->getJson('/api/admin/me')->assertUnauthorized();
});

it('requires a password', function () {
    $this->postJson('/api/admin/login', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('password');
});

it('returns 401 json for guests on protected routes', function () {
    $this->getJson('/api/admin/me')->assertUnauthorized();
    $this->postJson('/api/admin/logout')->assertUnauthorized();
    $this->get('/api/admin/me')->assertUnauthorized();
});

it('logs out and ends the session', function () {
    $this->postJson('/api/admin/login', ['password' => 'sir-parol-123'])->assertNoContent();

    $this->postJson('/api/admin/logout')->assertNoContent();

    $this->app['auth']->forgetGuards();

    $this->getJson('/api/admin/me')->assertUnauthorized();
});

it('rate limits repeated login attempts', function () {
    foreach (range(1, 5) as $_) {
        $this->postJson('/api/admin/login', ['password' => 'xato'])->assertUnprocessable();
    }

    $this->postJson('/api/admin/login', ['password' => 'sir-parol-123'])
        ->assertTooManyRequests();
});

it('refuses login from a non-stateful origin', function () {
    $this->withHeaders(['Origin' => 'https://boshqa-sayt.example'])
        ->postJson('/api/admin/login', ['password' => 'sir-parol-123'])
        ->assertForbidden();
});

it('exposes the csrf cookie endpoint', function () {
    $this->get('/sanctum/csrf-cookie')->assertNoContent();
});

it('allows the frontend origin with credentials', function () {
    $this->withHeaders(['Origin' => 'http://localhost:3000'])
        ->get('/sanctum/csrf-cookie')
        ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:3000')
        ->assertHeader('Access-Control-Allow-Credentials', 'true');
});
