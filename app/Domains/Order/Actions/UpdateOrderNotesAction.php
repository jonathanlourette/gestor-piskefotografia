<?php

declare(strict_types=1);

namespace App\Domains\Order\Actions;

use App\Domains\Order\Order;
use App\Support\Action;
use App\Support\Exceptions\BusinessException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class UpdateOrderNotesAction extends Action
{
    /**
     * @throws BusinessException
     */
    public function perform(): mixed
    {
        $this->validate();

        try {
            /** @var Order $order */
            $order = Order::findOrFail($this->data->get('id'));
            $order->notes = $this->data->get('notes');
            $order->save();

            return $order->fresh();
        } catch (ModelNotFoundException $e) {
            throw new BusinessException('Pedido não encontrado.');
        } catch (\Throwable $e) {
            report($e);
            throw new BusinessException('Não foi possível atualizar as notas do pedido. Tente novamente.');
        }
    }

    private function validate(): void
    {
        $this->data->validate([
            'id' => ['required', 'integer'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'id.required' => 'O ID do pedido é obrigatório.',
            'notes.max' => 'As notas não podem exceder 1000 caracteres.',
        ]);
    }
}
