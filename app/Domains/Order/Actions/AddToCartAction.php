<?php

declare(strict_types=1);

namespace App\Domains\Order\Actions;

use App\Domains\Product\Product;
use App\Support\Action;
use App\Support\Exceptions\BusinessException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class AddToCartAction extends Action
{
    /**
     * @throws BusinessException
     */
    public function perform(): mixed
    {
        $this->validate();

        try {
            /** @var Product $product */
            $product = Product::findOrFail($this->data->get('product_id'));

            if (! $product->isActive()) {
                throw new BusinessException('Este produto não está disponível para compra.');
            }

            $cart = $this->data->get('cart', []);

            $found = false;
            foreach ($cart as &$item) {
                if ($item['product_id'] === $product->id) {
                    $item['quantity']++;
                    $found = true;
                    break;
                }
            }

            if (! $found) {
                $cart[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => (float) $product->price,
                    'photo_limit' => $product->photo_limit,
                    'quantity' => 1,
                ];
            }

            return [
                'cart' => $cart,
                'product_name' => $product->name,
            ];
        } catch (ModelNotFoundException $e) {
            throw new BusinessException('Produto não encontrado.');
        } catch (BusinessException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            throw new BusinessException('Não foi possível adicionar o produto ao carrinho. Tente novamente.');
        }
    }

    private function validate(): void
    {
        $this->data->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'cart' => ['nullable', 'array'],
        ], [
            'product_id.required' => 'O produto é obrigatório.',
            'product_id.exists' => 'Produto não encontrado.',
        ]);
    }
}
