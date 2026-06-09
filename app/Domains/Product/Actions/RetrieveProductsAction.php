<?php

declare(strict_types=1);

namespace App\Domains\Product\Actions;

use App\Domains\Product\Enums\ProductTypeEnum;
use App\Domains\Product\Product;
use App\Support\Action;
use App\Support\Exceptions\BusinessException;

final class RetrieveProductsAction extends Action
{
    /**
     * @throws BusinessException
     */
    public function perform(): mixed
    {
        $this->validate();

        try {
            $query = Product::query();

            if ($this->data->has('active')) {
                $active = filter_var($this->data->get('active'), FILTER_VALIDATE_BOOLEAN);
                $query->where('active', $active);
            }

            if ($this->data->has('type')) {
                $type = ProductTypeEnum::from($this->data->get('type'));
                $query->byType($type);
            }

            return $query->orderBy('id', 'desc')->paginate()->withQueryString();
        } catch (\Throwable $e) {
            report($e);
            throw new BusinessException('Não foi possível carregar a listagem de produtos. Tente novamente.');
        }
    }

    private function validate(): void
    {
        $this->data->validate([
            'active' => ['nullable', 'boolean'],
            'type' => ['nullable', 'string', ProductTypeEnum::validationRule()],
        ]);
    }
}
