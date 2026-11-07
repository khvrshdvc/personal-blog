<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:120,1')->group(function () {
    Route::get('posts', [PostController::class, 'index']);
    Route::get('posts/{slug}', [PostController::class, 'show']);
    Route::get('tags', [PostController::class, 'tags']);
    Route::get('search', SearchController::class);
    Route::get('projects', [ProjectController::class, 'index']);
    Route::get('settings', SettingsController::class);
});

Route::prefix('admin')->group(function () {
    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:admin-login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});
