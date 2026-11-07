<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\ExternalController;
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
    Route::get('activity', [ExternalController::class, 'activity']);
    Route::get('runs', [ExternalController::class, 'runs']);
    Route::get('projects/{project}/runs', [ExternalController::class, 'projectRuns'])->whereNumber('project');
    Route::get('projects/{project}/github', [ExternalController::class, 'projectGithub'])->whereNumber('project');
    Route::get('telegram', [ExternalController::class, 'telegram']);
});

Route::prefix('admin')->group(function () {
    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:admin-login');

    Route::middleware(['auth:sanctum', 'throttle:300,1'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);

        Route::get('dashboard', DashboardController::class);

        Route::get('posts', [AdminPostController::class, 'index']);
        Route::post('posts', [AdminPostController::class, 'store']);
        Route::get('posts/{post}', [AdminPostController::class, 'show'])->whereNumber('post');
        Route::patch('posts/{post}', [AdminPostController::class, 'update'])->whereNumber('post');
        Route::delete('posts/{post}', [AdminPostController::class, 'destroy'])->whereNumber('post');
        Route::post('posts/{post}/publish', [AdminPostController::class, 'publish'])->whereNumber('post');
        Route::post('posts/{post}/apply', [AdminPostController::class, 'apply'])->whereNumber('post');
        Route::delete('posts/{post}/pending', [AdminPostController::class, 'discard'])->whereNumber('post');
        Route::post('posts/{post}/unpublish', [AdminPostController::class, 'unpublish'])->whereNumber('post');
        Route::post('preview', [AdminPostController::class, 'preview']);
        Route::post('uploads', [AdminPostController::class, 'upload']);

        Route::get('projects', [AdminProjectController::class, 'index']);
        Route::post('projects', [AdminProjectController::class, 'store']);
        Route::patch('projects/{project}', [AdminProjectController::class, 'update'])->whereNumber('project');
        Route::delete('projects/{project}', [AdminProjectController::class, 'destroy'])->whereNumber('project');

        Route::get('settings', [AdminSettingsController::class, 'show']);
        Route::put('settings', [AdminSettingsController::class, 'update']);
    });
});
