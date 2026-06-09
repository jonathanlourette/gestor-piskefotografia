<?php

declare(strict_types=1);

namespace App\Domains\Product\Actions;

use App\Domains\Product\Enums\ProductTypeEnum;
use App\Domains\Product\Product;
use App\Support\Action;
use App\Support\Exceptions\BusinessException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class UpdateProductAction extends Action
{
    /**
     * @throws BusinessException
     */
    public function perform(): mixed
    {
        $this->validate();

        try {
            /** @var Product $product */
            $product = Product::findOrFail($this->data->get('id'));

            DB::transaction(function () use ($product) {
                $name = $this->data->get('name');

                if ($name !== null) {
                    $product->name = strip_tags($name);
                    $product->slug = Str::slug($name);
                }

                if ($this->data->has('description')) {
                    $product->description = $this->data->get('description');
                }

                if ($this->data->has('price')) {
                    $product->price = $this->data->get('price');
                }

                if ($this->data->has('photo_limit')) {
                    $product->photo_limit = $this->data->get('photo_limit');
                }

                if ($this->data->has('type')) {
                    $product->type = ProductTypeEnum::from($this->data->get('type'));
                }

                if ($this->data->has('remove_image') && $this->data->get('remove_image') === '1') {
                    $this->deleteExistingImage($product);
                    $product->image_path = null;
                } elseif ($this->data->has('image') && $this->data->get('image') instanceof UploadedFile) {
                    $this->deleteExistingImage($product);
                    $product->image_path = $this->handleImageUpload();
                }

                if ($this->data->has('active')) {
                    $product->active = $this->data->get('active');
                }

                $product->save();
            });

            return $product->fresh();
        } catch (ModelNotFoundException $e) {
            throw new BusinessException('Produto não encontrado para atualização.');
        } catch (\Throwable $e) {
            report($e);
            throw new BusinessException('Não foi possível atualizar o produto. Tente novamente.');
        }
    }

    private function validate(): void
    {
        $this->data->validate([
            'id' => ['required', 'integer'],
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'photo_limit' => ['nullable', 'integer', 'min:1'],
            'type' => ['nullable', 'string', ProductTypeEnum::validationRule()],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
        ], [
            'id.required' => 'O ID do produto é obrigatório.',
            'name.max' => 'O nome do produto não pode exceder 255 caracteres.',
            'price.numeric' => 'O preço deve ser um número.',
            'price.min' => 'O preço não pode ser negativo.',
            'photo_limit.integer' => 'O limite de fotos deve ser um número inteiro.',
            'photo_limit.min' => 'O limite de fotos deve ser pelo menos 1.',
            'type.in' => 'O tipo do produto deve ser um dos valores válidos: pacote_fotos, quadro, ima, album.',
        ]);
    }

    /**
     * Delete the existing image file from disk.
     */
    private function deleteExistingImage(Product $product): void
    {
        if ($product->image_path && File::exists(public_path($product->image_path))) {
            File::delete(public_path($product->image_path));
        }
    }

    /**
     * Handle the image upload and return the relative path.
     */
    private function handleImageUpload(): string
    {
        /** @var UploadedFile $image */
        $image = $this->data->get('image');

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
