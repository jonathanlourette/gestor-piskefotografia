<?php

use App\Apps\Admin\Providers\AdminServiceProvider;
use App\Apps\Gallery\Providers\GalleryServiceProvider;
use App\Apps\Site\Providers\SiteServiceProvider;
use App\Core\Providers\AppServiceProvider;
use App\Integrations\IntegrationServiceProvider;

return [
    AppServiceProvider::class,
    IntegrationServiceProvider::class,
    SiteServiceProvider::class,
    AdminServiceProvider::class,
    GalleryServiceProvider::class,
];
