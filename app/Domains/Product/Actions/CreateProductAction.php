<?php

declare(strict_types=1);

namespace App\Domains\Product\Actions;

use App\Domains\Product\Enums\ProductTypeEnum;
use App\Domains\Product\Product;
use App\Support\Action;
use App\Support\Exceptions\BusinessException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class CreateProductAction extends Action
{
    /**
     * @throws BusinessException
     */
    public function perform(): mixed
    {
        $this->validate();

        try {
            $product = new Product;
            $product->name = strip_tags($this->data->get('name'));
            $product->slug = Str::slug($this->data->get('name'));
            $product->description = $this->data->get('description');
            $product->price = $this->data->get('price');
            $product->photo_limit = $this->data->get('photo_limit');
            $product->type = ProductTypeEnum::from($this->data->get('type'));
            $product->image_path = $this->handleImageUpload();
            $product->active = $this->data->get('active', true);
            $product->save();

            return $product;
        } catch (\Throwable $e) {
            report($e);
            throw new BusinessException('Não foi possível criar o produto. Tente novamente.');
        }
    }

    private function validate(): void
    {
        $this->data->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'photo_limit' => ['required', 'integer', 'min:1'],
            'type' => ['required', 'string', ProductTypeEnum::validationRule()],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'O nome do produto é obrigatório.',
            'name.max' => 'O nome do produto não pode exceder 255 caracteres.',
            'price.required' => 'O preço do produto é obrigatório.',
            'price.numeric' => 'O preço deve ser um número.',
            'price.min' => 'O preço não pode ser negativo.',
            'photo_limit.required' => 'O limite de fotos é obrigatório.',
            'photo_limit.integer' => 'O limite de fotos deve ser um número inteiro.',
            'photo_limit.min' => 'O limite de fotos deve ser pelo menos 1.',
            'type.required' => 'O tipo do produto é obrigatório.',
            'type.in' => 'O tipo do produto deve ser um dos valores válidos: pacote_fotos, quadro, ima, album.',
        ]);
    }

    /**
     * Handle the image upload and return the relative path.
     */
    private function handleImageUpload(): ?string
    {
        $image = $this->data->get('image');

        if (! ($image instanceof UploadedFile) || ! $image->isValid()) {
            return null;
        }

        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower($image->getClientOriginalExtension());

        if (! in_array($ext, $allowed, true)) {
            throw new BusinessException('Extensão de arquivo inválida.');
        }

        $directory = public_path('assets/img/products');

        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $filename = Str::slug(pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME))
            .'-'
            .Str::random(6)
            .'.'
            .$image->getClientOriginalExtension();

        $image->move($directory, $filename);

        return 'assets/img/products/'.$filename;
    }
}
