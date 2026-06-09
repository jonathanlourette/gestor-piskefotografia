<?php

declare(strict_types=1);

namespace App\Domains\Order\Actions;

use App\Support\Action;
use App\Support\Exceptions\BusinessException;

final class RemoveFromCartAction extends Action
{
    /**
     * @throws BusinessException
     */
    public function perform(): mixed
    {
        $this->validate();

        try {
            $productId = $this->data->get('product_id');
            $cart = $this->data->get('cart', []);

            $cart = collect($cart)
                ->filter(fn (array $item) => $item['product_id'] !== $productId)
                ->values()
                ->toArray();

            return $cart;
        } catch (\Throwable $e) {
            report($e);
            throw new BusinessException('Não foi possível remover o produto do carrinho. Tente novamente.');
        }
    }

    private function validate(): void
    {
        $this->data->validate([
            'product_id' => ['required', 'integer'],
            'cart' => ['nullable', 'array'],
        ], [
            'product_id.required' => 'O produto é obrigatório.',
        ]);
    }
}
