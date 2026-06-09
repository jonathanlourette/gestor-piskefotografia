<?php

declare(strict_types=1);

namespace App\Domains\Order\Actions;

use App\Support\Action;
use App\Support\Exceptions\BusinessException;

final class ClearCartAction extends Action
{
    /**
     * @throws BusinessException
     */
    public function perform(): mixed
    {
        try {
            return true;
        } catch (\Throwable $e) {
            report($e);
            throw new BusinessException('Não foi possível limpar o carrinho. Tente novamente.');
        }
    }
}
