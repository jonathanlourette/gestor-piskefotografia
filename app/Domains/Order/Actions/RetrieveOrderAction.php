<?php

declare(strict_types=1);

namespace App\Domains\Order\Actions;

use App\Domains\Order\Order;
use App\Support\Action;
use App\Support\Exceptions\BusinessException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class RetrieveOrderAction extends Action
{
    /**
     * @throws BusinessException
     */
    public function perform(): mixed
    {
        $this->validate();

        try {
            $id = $this->data->get('id');
            $uuid = $this->data->get('uuid');

            $query = Order::query();

            if ($uuid) {
                $query->where('uuid', $uuid);
            } else {
                $query->where('id', $id);
            }

            /** @var Order|null $order */
            $order = $query->firstOrFail();

            $order->load('items.product', 'items.photos');

            return $order;
        } catch (ModelNotFoundException $e) {
            throw new BusinessException('Pedido não encontrado.');
        } catch (\Throwable $e) {
            report($e);
            throw new BusinessException('Não foi possível buscar o pedido. Tente novamente.');
        }
    }

    private function validate(): void
    {
        $this->data->validate([
            'id' => ['nullable', 'integer', 'required_without:uuid'],
            'uuid' => ['nullable', 'string', 'required_without:id'],
        ], [
            'id.required_without' => 'Informe o ID ou UUID do pedido.',
            'uuid.required_without' => 'Informe o ID ou UUID do pedido.',
        ]);
    }
}
