<?php

declare(strict_types=1);

namespace App\Domains\Product\Actions;

use App\Domains\Product\Product;
use App\Support\Action;
use App\Support\Exceptions\BusinessException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class RemoveProductAction extends Action
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

            $product->delete();

            return true;
        } catch (ModelNotFoundException $e) {
            throw new BusinessException('Produto não encontrado para exclusão.');
        } catch (\Throwable $e) {
            report($e);
            throw new BusinessException('Não foi possível excluir o produto. Tente novamente.');
        }
    }

    private function validate(): void
    {
        $this->data->validate([
            'id' => ['required', 'integer'],
        ]);
    }
}
