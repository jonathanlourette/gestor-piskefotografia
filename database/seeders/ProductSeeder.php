<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Product\Enums\ProductTypeEnum;
use App\Domains\Product\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Seed the products table.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => '20 Fotos',
                'slug' => '20-fotos',
                'price' => 60.00,
                'photo_limit' => 20,
                'type' => ProductTypeEnum::PACOTE_FOTOS->value,
                'active' => true,
            ],
            [
                'name' => '40 Fotos',
                'slug' => '40-fotos',
                'price' => 120.00,
                'photo_limit' => 40,
                'type' => ProductTypeEnum::PACOTE_FOTOS->value,
                'active' => true,
            ],
            [
                'name' => '80 Fotos',
                'slug' => '80-fotos',
                'price' => 250.00,
                'photo_limit' => 80,
                'type' => ProductTypeEnum::PACOTE_FOTOS->value,
                'active' => true,
            ],
            [
                'name' => 'Polaroid Imã',
                'slug' => 'polaroid-ima',
                'price' => 15.00,
                'photo_limit' => 1,
                'type' => ProductTypeEnum::IMA->value,
                'image_path' => 'assets/img/prod_extra_4.jpg',
                'active' => true,
            ],
            [
                'name' => 'Quadro',
                'slug' => 'quadro',
                'price' => 15.00,
                'photo_limit' => 1,
                'type' => ProductTypeEnum::QUADRO->value,
                'image_path' => 'assets/img/prod_extra_1.jpg',
                'active' => true,
            ],
            [
                'name' => 'Álbum',
                'slug' => 'album',
                'price' => 15.00,
                'photo_limit' => 20,
                'type' => ProductTypeEnum::ALBUM->value,
                'image_path' => 'assets/img/prod_extra_2.jpg',
                'active' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                ['slug' => $product['slug']],
                $product
            );
        }
    }
}
