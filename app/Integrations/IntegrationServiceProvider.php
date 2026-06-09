<?php

declare(strict_types=1);

namespace App\Integrations;

use App\Integrations\Storage\Contract\StorageServiceInterface;
use App\Integrations\Storage\Services\S3StorageService;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider responsável por registrar os bindings
 * de todas as integrações externas da aplicação.
 */
class IntegrationServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            StorageServiceInterface::class,
            S3StorageService::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
