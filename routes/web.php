<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
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

    Route::middleware('auth')->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::post('logout', [AuthController::class, 'destroy'])->name('logout');
    });
});
