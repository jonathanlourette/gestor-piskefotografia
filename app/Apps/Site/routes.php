<?php

declare(strict_types=1);

use App\Apps\Site\Controllers\CartController;
use App\Apps\Site\Controllers\LandingController;
use App\Apps\Site\Controllers\OrderController;
use App\Apps\Site\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;

/** Landing */
Route::controller(LandingController::class)->group(function () {
    Route::get('/', 'index')->name('site.landing.index');
});

/** Cart */
Route::controller(CartController::class)->prefix('carrinho')->name('site.cart.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/adicionar', 'add')->name('add');
    Route::post('/remover', 'remove')->name('remove');
    Route::post('/limpar', 'clear')->name('clear');
});

/** Orders */
Route::controller(OrderController::class)->prefix('pedido')->name('site.order.')->group(function () {
    Route::get('/criar', 'create')->name('create');
    Route::post('/', 'store')->name('store');
    Route::get('/{id}/upload', 'upload')->name('upload');
    Route::post('/{id}/foto', 'uploadPhoto')->name('uploadPhoto');
    Route::post('/{id}/foto/remover', 'removePhoto')->name('removePhoto');
    Route::post('/{id}/finalizar', 'finalize')->name('finalize');
    Route::get('/{id}/confirmacao', 'confirmation')->name('confirmation');
});

/** Tracking */
Route::controller(TrackingController::class)->prefix('rastreio')->name('site.tracking.')->group(function () {
    Route::get('/{uuid}', 'show')->name('show');
});
