<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\DasturlashController;
use App\Http\Controllers\FikrlarController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MenController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/fikrlar', [FikrlarController::class, 'index'])->name('fikrlar');
Route::get('/fikrlar/qidiruv', [FikrlarController::class, 'search'])
    ->middleware('throttle:60,1')
    ->name('fikrlar.search');
Route::get('/dasturlash', [DasturlashController::class, 'yozuvlar'])->name('dasturlash');
Route::get('/yozuvlar', [DasturlashController::class, 'yozuvlar'])->name('yozuvlar');
Route::get('/loyihalar', [DasturlashController::class, 'loyihalar'])->name('loyihalar');
Route::get('/faollik', [DasturlashController::class, 'faollik'])->name('faollik');
Route::get('/men', MenController::class)->name('men');
Route::get('/post/{slug}', [PostController::class, 'show'])->name('post.show');

Route::get('/qidiruv', SearchController::class)
    ->middleware('throttle:60,1')
    ->name('search');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'create'])->name('login');
        Route::post('login', [AuthController::class, 'store'])
            ->middleware('throttle:admin-login')
            ->name('login.store');
    });

    Route::middleware(['auth', 'throttle:300,1'])->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::post('logout', [AuthController::class, 'destroy'])->name('logout');

        Route::controller(AdminPostController::class)->prefix('posts')->name('posts.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('new/{category?}', 'create')->where('category', 'fikr|dev')->name('create');
            Route::post('cover', 'cover')->name('cover');
            Route::post('preview', 'preview')->name('preview');
            Route::post('/', 'store')->name('store');
            Route::get('{post:id}', 'edit')->whereNumber('post')->name('edit');
            Route::put('{post:id}', 'update')->whereNumber('post')->name('update');
            Route::post('{post:id}/publish', 'publish')->whereNumber('post')->name('publish');
            Route::post('{post:id}/discard', 'discard')->whereNumber('post')->name('discard');
            Route::post('{post:id}/unpublish', 'unpublish')->whereNumber('post')->name('unpublish');
            Route::delete('{post:id}', 'destroy')->whereNumber('post')->name('destroy');
        });

        Route::controller(AdminProjectController::class)->prefix('projects')->name('projects.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('new', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('{project}', 'edit')->whereNumber('project')->name('edit');
            Route::put('{project}', 'update')->whereNumber('project')->name('update');
            Route::delete('{project}', 'destroy')->whereNumber('project')->name('destroy');
        });

        Route::get('settings', [AdminSettingsController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [AdminSettingsController::class, 'update'])->name('settings.update');
    });
});
