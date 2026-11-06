<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.home')->name('home');
Route::view('/fikrlar', 'pages.fikrlar')->name('fikrlar');
Route::view('/dasturlash', 'pages.dasturlash')->name('dasturlash');
Route::view('/yozuvlar', 'pages.dasturlash')->name('yozuvlar');
Route::view('/loyihalar', 'pages.dasturlash')->name('loyihalar');
Route::view('/faollik', 'pages.dasturlash')->name('faollik');
Route::view('/men', 'pages.men')->name('men');

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
