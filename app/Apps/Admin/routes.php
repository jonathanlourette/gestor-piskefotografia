<?php

declare(strict_types=1);

use App\Apps\Admin\Controllers\AuthController;
use App\Apps\Admin\Controllers\DashboardController;
use App\Apps\Admin\Controllers\OrderController;
use App\Apps\Admin\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

/** Admin Auth Routes (Public) */
Route::middleware(['web'])->prefix('admin')->name('admin.')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::get('/login', 'showLogin')->name('login');
        Route::post('/login', 'login')->name('login.post');
        Route::post('/logout', 'logout')->name('logout');
    });
});

/** Admin Authenticated Routes */
Route::middleware(['web', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    /** Dashboard */
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/', 'index')->name('dashboard.index');
    });

    /** Products */
    Route::controller(ProductController::class)->prefix('produtos')->name('products.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/criar', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}/editar', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'delete')->name('delete');
    });

    /** Orders */
    Route::controller(OrderController::class)->prefix('pedidos')->name('orders.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{id}', 'show')->name('show');
        Route::delete('/{id}', 'delete')->name('delete');
        Route::put('/{id}/status', 'updateStatus')->name('updateStatus');
        Route::get('/{id}/fotos', 'photos')->name('photos');
        Route::put('/{id}/notas', 'updateNotes')->name('updateNotes');
    });
});
