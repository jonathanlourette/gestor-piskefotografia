<?php

declare(strict_types=1);

namespace App\Domains\Product\Actions;

use App\Domains\Product\Product;
use App\Support\Action;
use App\Support\Exceptions\BusinessException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class RetrieveProductAction extends Action
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

            return $product;
        } catch (ModelNotFoundException $e) {
            throw new BusinessException('Produto não encontrado.');
        } catch (\Throwable $e) {
            report($e);
            throw new BusinessException('Não foi possível buscar o produto. Tente novamente.');
        }
    }

    private function validate(): void
    {
        $this->data->validate([
            'id' => ['required', 'integer'],
        ]);
    }
}
