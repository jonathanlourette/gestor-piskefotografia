<?php

declare(strict_types=1);

namespace App\Domains\Order\Actions;

use App\Support\Action;
use App\Support\Exceptions\BusinessException;

final class RetrieveCartAction extends Action
{
    /**
     * @throws BusinessException
     */
    public function perform(): mixed
    {
        try {
            $cart = $this->data->get('cart', []);

            $total = collect($cart)->sum(fn (array $item) => $item['price'] * $item['quantity']);
            $count = collect($cart)->sum('quantity');

            return [
                'items' => $cart,
                'total' => number_format($total, 2, '.', ''),
                'count' => $count,
            ];
        } catch (\Throwable $e) {
            report($e);
            throw new BusinessException('Não foi possível recuperar o carrinho. Tente novamente.');
        }
    }
}
