<?php

declare(strict_types=1);

use App\Apps\Gallery\Controllers\Admin\GalleryController;
use App\Apps\Gallery\Controllers\Admin\GalleryPhotoController;
use App\Apps\Gallery\Controllers\Public\GalleryAuthController;
use App\Apps\Gallery\Controllers\Public\GalleryViewController;
use App\Apps\Gallery\Middleware\GalleryAccess;
use Illuminate\Support\Facades\Route;

/** Admin Authenticated Routes */
Route::middleware(['web', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    /** Gallery Management */
    Route::controller(GalleryController::class)->prefix('galerias')->name('gallery.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/criar', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}/editar', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::post('/{id}/ativar', 'activate')->name('activate');
    });

    /** Gallery Photos Management */
    Route::controller(GalleryPhotoController::class)->prefix('galerias/{id}/fotos')->name('gallery.photos.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::delete('/', 'destroy')->name('destroy');
        Route::post('/capa', 'setCover')->name('setCover');
    });
});

/** Public Gallery Routes (no admin middleware) */
Route::middleware(['web'])->group(function () {
    Route::prefix('galeria')->name('gallery.')->group(function () {
        Route::get('/{uuid}', [GalleryAuthController::class, 'showLogin'])->name('login');
        Route::post('/{uuid}/auth', [GalleryAuthController::class, 'authenticate'])->name('auth');

        // Protected routes (require gallery session auth)
        Route::middleware([GalleryAccess::class])->group(function () {
            Route::get('/{uuid}/showcase', [GalleryViewController::class, 'showcase'])->name('showcase');
            Route::get('/{uuid}/fotos', [GalleryViewController::class, 'photos'])->name('photos');
            Route::get('/{uuid}/fotos/{photoId}', [GalleryViewController::class, 'photo'])->name('photo');
            Route::post('/{uuid}/favoritos/{photoId}', [GalleryViewController::class, 'toggleFavorite'])->name('favorite');
            Route::get('/{uuid}/download', [GalleryViewController::class, 'downloadZip'])->name('download');
        });
    });
});
