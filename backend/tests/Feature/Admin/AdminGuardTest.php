<?php

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;

it('requires auth and a rate limit on every admin route except login', function () {
    $routes = collect(RouteFacade::getRoutes()->getRoutes())
        ->filter(fn (Route $route) => str_starts_with($route->uri(), 'api/admin'))
        ->reject(fn (Route $route) => $route->uri() === 'api/admin/login');

    expect($routes->count())->toBeGreaterThan(18);

    foreach ($routes as $route) {
        expect($route->gatherMiddleware())->toContain('auth:sanctum');
        expect(preg_grep('/^throttle:/', $route->gatherMiddleware()))->not->toBeEmpty();
    }
});

it('answers 401 json to guests on every admin endpoint', function () {
    $calls = [
        ['get', '/api/admin/dashboard'],
        ['get', '/api/admin/posts'],
        ['post', '/api/admin/posts'],
        ['get', '/api/admin/posts/1'],
        ['patch', '/api/admin/posts/1'],
        ['delete', '/api/admin/posts/1'],
        ['post', '/api/admin/posts/1/publish'],
        ['post', '/api/admin/posts/1/apply'],
        ['delete', '/api/admin/posts/1/pending'],
        ['post', '/api/admin/posts/1/unpublish'],
        ['post', '/api/admin/preview'],
        ['post', '/api/admin/uploads'],
        ['get', '/api/admin/projects'],
        ['post', '/api/admin/projects'],
        ['post', '/api/admin/projects/sync'],
        ['patch', '/api/admin/projects/1'],
        ['delete', '/api/admin/projects/1'],
        ['get', '/api/admin/settings'],
        ['put', '/api/admin/settings'],
    ];

    foreach ($calls as [$method, $uri]) {
        $this->{$method.'Json'}($uri)->assertUnauthorized();
    }
});
