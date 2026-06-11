<?php

declare(strict_types=1);

namespace App\Domains\Order\Actions;

use App\Domains\Order\Order;
use App\Domains\Order\OrderItem;
use App\Domains\Product\Product;
use App\Support\Action;
use App\Support\Exceptions\BusinessException;
use Illuminate\Support\Facades\DB;

final class CreateOrderAction extends Action
{
    /**
     * @throws BusinessException
     */
    public function perform(): mixed
    {
        $this->validate();

        try {
            return DB::transaction(function () {
                $order = new Order;
                $order->customer_name = strip_tags($this->data->get('customer_name'));
                $order->customer_phone = strip_tags($this->data->get('customer_phone'));
                $order->notes = $this->data->get('notes');
                $order->save();

                /** @var array<int, array{product_id: int, name: string, price: float, photo_limit: int, quantity: int}> $items */
                $items = $this->data->get('items', []);

                foreach ($items as $itemData) {
                    $product = Product::where('id', (int) $itemData['product_id'])
                        ->active()
                        ->first();

                    if ($product === null) {
                        throw new BusinessException('Produto não encontrado ou indisponível.');
                    }

                    $quantity = max(1, (int) ($itemData['quantity'] ?? 1));

                    // Cada unidade do pacote vira um item separado, com upload de fotos próprio
                    for ($i = 0; $i < $quantity; $i++) {
                        $item = new OrderItem;
                        $item->order_id = $order->id;
                        $item->product_id = $product->id;
                        $item->quantity = 1;
                        $item->unit_price = $product->price;
                        $item->save();
                    }
                }

                $order->load('items');

                return $order;
            });
        } catch (\Throwable $e) {
            report($e);
            throw new BusinessException('Não foi possível criar o pedido. Tente novamente.');
        }
    }

    private function validate(): void
    {
        $this->data->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'celular_com_ddd'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ], [
            'customer_name.required' => 'O nome do cliente é obrigatório.',
            'customer_phone.required' => 'O telefone do cliente é obrigatório.',
            'customer_phone.celular_com_ddd' => 'Informe um telefone válido com DDD. Ex: (27) 99999-9999',
            'items.required' => 'O pedido deve conter ao menos um item.',
        ]);
    }
}
