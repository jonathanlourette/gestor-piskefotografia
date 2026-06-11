<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Product\Enums\ProductTypeEnum;
use App\Domains\Product\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ProductSeeder extends Seeder
{
    /**
     * Seed the products table.
     */
    public function run(): void
    {
        $this->ensureProductImages();

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
                'image_path' => 'products/polaroid-ima.jpg',
                'active' => true,
            ],
            [
                'name' => 'Quadro',
                'slug' => 'quadro',
                'price' => 15.00,
                'photo_limit' => 1,
                'type' => ProductTypeEnum::QUADRO->value,
                'image_path' => 'products/quadro.jpg',
                'active' => true,
            ],
            [
                'name' => 'Álbum',
                'slug' => 'album',
                'price' => 15.00,
                'photo_limit' => 20,
                'type' => ProductTypeEnum::ALBUM->value,
                'image_path' => 'products/album.jpg',
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

    /**
     * Copy seed images to storage/app/public/products/ if they don't exist.
     * Source images live in resources/assets/seed/products/.
     */
    private function ensureProductImages(): void
    {
        $sourceDir = resource_path('assets/seed/products');

        if (! File::isDirectory($sourceDir)) {
            $this->command->warn('Diretório de imagens seed não encontrado: '.$sourceDir);

            return;
        }

        foreach (File::files($sourceDir) as $file) {
            $path = 'products/'.$file->getFilename();

            if (! Storage::disk('public')->exists($path)) {
                Storage::disk('public')->put($path, file_get_contents($file->getPathname()));
                $this->command->info('Imagem copiada: '.$file->getFilename());
            }
        }
    }
}
