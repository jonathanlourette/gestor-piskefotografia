<?php

declare(strict_types=1);

namespace App\Apps\Site\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class SiteServiceProvider extends ServiceProvider
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
        // Register Site views
        $this->loadViewsFrom(
            app_path('Apps/Site/Views'),
            'site'
        );

        // Register Site routes
        Route::middleware(['web'])
            ->group(app_path('Apps/Site/routes.php'));
    }
}
