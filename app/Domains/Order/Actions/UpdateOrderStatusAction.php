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

                // ENVIADO/PROCESSANDO só pode ir para PAGO
                // PAGO, REVELANDO, CONCLUIDO transitam livremente entre si
                $restrictedStatuses = [
                    OrderStatusEnum::ENVIADO->value,
                    OrderStatusEnum::PROCESSANDO->value,
                    OrderStatusEnum::PROCESSADO->value,
                ];

                $freeStatuses = [
                    OrderStatusEnum::PAGO->value,
                    OrderStatusEnum::REVELANDO->value,
                    OrderStatusEnum::CONCLUIDO->value,
                ];

                if (in_array($order->status->value, $restrictedStatuses, true)) {
                    if (! in_array($newStatus->value, $freeStatuses, true)) {
                        throw new BusinessException("Não é possível alterar o status de \"{$order->status->label()}\" para \"{$newStatus->label()}\".");
                    }
                } elseif (! in_array($order->status->value, $freeStatuses, true)) {
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
