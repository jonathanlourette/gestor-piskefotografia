<?php

declare(strict_types=1);

namespace App\Apps\Site\Controllers;

use App\Domains\Product\Enums\ProductTypeEnum;
use App\Domains\Product\Product;
use Illuminate\View\View;

class LandingController extends BaseSiteController
{
    public function index(): View
    {
        $pacotes = Product::active()
            ->byType(ProductTypeEnum::PACOTE_FOTOS)
            ->get();

        $outros = Product::active()
            ->whereNot('type', ProductTypeEnum::PACOTE_FOTOS->value)
            ->get();

        return view('site::landing.index', compact('pacotes', 'outros'));
    }
}
