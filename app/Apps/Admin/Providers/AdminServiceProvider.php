<?php

declare(strict_types=1);

namespace App\Apps\Admin\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AdminServiceProvider extends ServiceProvider
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
        // Register Admin views
        $this->loadViewsFrom(
            app_path('Apps/Admin/Views'),
            'admin'
        );

        // Register Admin routes
        Route::middleware(['web'])
            ->group(app_path('Apps/Admin/routes.php'));
    }
}
