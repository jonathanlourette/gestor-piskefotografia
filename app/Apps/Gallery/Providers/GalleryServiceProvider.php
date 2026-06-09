<?php

declare(strict_types=1);

namespace App\Apps\Gallery\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class GalleryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register Gallery views
        $this->loadViewsFrom(
            app_path('Apps/Gallery/Views'),
            'gallery'
        );

        // Register Gallery routes
        Route::middleware(['web'])
            ->group(app_path('Apps/Gallery/routes.php'));
    }
}
