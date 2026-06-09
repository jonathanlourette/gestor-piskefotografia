<?php

declare(strict_types=1);

namespace App\Domains\Order\Actions;

use App\Domains\Order\Enums\OrderStatusEnum;
use App\Domains\Order\Order;
use App\Support\Action;
use App\Support\Exceptions\BusinessException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

final class UpdateOrderStatusAction extends Action
{
    /**
     * @throws BusinessException
     */
    public function perform(): mixed
    {
        $this->validate();

        try {
            return DB::transaction(function () {
                /** @var Order $order */
                $order = Order::findOrFail($this->data->get('order_id'));

                $newStatus = OrderStatusEnum::from((int) $this->data->get('status'));

                $transitions = [
                    OrderStatusEnum::ENVIADO->value => OrderStatusEnum::PAGO->value,
                    OrderStatusEnum::PAGO->value => OrderStatusEnum::REVELANDO->value,
                    OrderStatusEnum::REVELANDO->value => OrderStatusEnum::CONCLUIDO->value,
                ];

                $allowed = $transitions[$order->status->value] ?? null;

                if ($allowed === null || $newStatus->value !== $allowed) {
                    throw new BusinessException("Não é possível alterar o status de \"{$order->status->label()}\" para \"{$newStatus->label()}\".");
                }

                $order->status = $newStatus;
                $order->save();

                return $order->fresh();
            });
        } catch (ModelNotFoundException $e) {
            throw new BusinessException('Pedido não encontrado.');
        } catch (BusinessException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            throw new BusinessException('Não foi possível atualizar o status do pedido.');
        }
    }

    private function validate(): void
    {
        $validStatuses = implode(',', array_column(OrderStatusEnum::cases(), 'value'));

        $this->data->validate([
            'order_id' => ['required', 'integer'],
            'status' => ['required', 'integer', 'in:'.$validStatuses],
        ], [
            'order_id.required' => 'O ID do pedido é obrigatório.',
            'status.required' => 'O novo status é obrigatório.',
            'status.in' => 'O status informado é inválido.',
        ]);
    }
}
