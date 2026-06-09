<?php

declare(strict_types=1);

namespace App\Domains\Order\Actions;

use App\Domains\Order\Enums\OrderStatusEnum;
use App\Domains\Order\Order;
use App\Support\Action;
use App\Support\Exceptions\BusinessException;

final class RetrieveOrdersAction extends Action
{
    /**
     * @throws BusinessException
     */
    public function perform(): mixed
    {
        $this->validate();

        try {
            $query = Order::query()
                ->with('items.product')
                ->latestFirst();

            $status = $this->data->get('status');

            if ($status) {
                $query->byStatus(OrderStatusEnum::from((int) $status));
            }

            $search = $this->data->get('search');

            if ($search) {
                $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $search);
                $query->where(function ($q) use ($escaped) {
                    $q->where('customer_name', 'like', "%{$escaped}%")
                        ->orWhere('customer_phone', 'like', "%{$escaped}%");
                });
            }

            $perPage = (int) $this->data->get('per_page', 15);

            return $query
                ->paginate($perPage)
                ->withQueryString();
        } catch (\Throwable $e) {
            report($e);
            throw new BusinessException('Não foi possível carregar a listagem de pedidos. Tente novamente.');
        }
    }

    private function validate(): void
    {
        $validStatuses = implode(',', array_column(OrderStatusEnum::cases(), 'value'));

        $this->data->validate([
            'status' => ['nullable', 'integer', 'in:'.$validStatuses],
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
    }
}
