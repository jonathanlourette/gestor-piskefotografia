<?php

declare(strict_types=1);

namespace App\Apps\Site\Controllers;

use App\Domains\Order\Actions\AddToCartAction;
use App\Domains\Order\Actions\ClearCartAction;
use App\Domains\Order\Actions\RemoveFromCartAction;
use App\Domains\Order\Actions\RetrieveCartAction;
use App\Support\Exceptions\BusinessException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends BaseSiteController
{
    /**
     * Retorna o estado do carrinho como JSON (para Alpine.js AJAX).
     */
    public function index(RetrieveCartAction $action): JsonResponse
    {
        $data = $action->setData(['cart' => session('cart', [])])->perform();

        return response()->json($data);
    }

    /**
     * Adiciona item ao carrinho.
     */
    public function add(Request $request, AddToCartAction $action): JsonResponse|RedirectResponse
    {
        try {
            $result = $action->setData([
                'product_id' => $request->input('product_id'),
                'cart' => session('cart', []),
            ])->perform();

            session(['cart' => $result['cart']]);

            if (! $request->expectsJson()) {
                return redirect()
                    ->route('site.landing.index')
                    ->with('success', "{$result['product_name']} adicionado ao carrinho!")
                    ->withFragment('pacotes');
            }

            return $this->getCartResponse('Produto adicionado ao carrinho!');
        } catch (BusinessException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Não foi possível realizar a operação. Tente novamente.',
            ], 500);
        }
    }

    /**
     * Remove item do carrinho.
     */
    public function remove(Request $request, RemoveFromCartAction $action): JsonResponse
    {
        try {
            $cart = $action->setData([
                'product_id' => $request->input('product_id'),
                'cart' => session('cart', []),
            ])->perform();

            session(['cart' => $cart]);

            return $this->getCartResponse('Produto removido do carrinho.');
        } catch (BusinessException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Não foi possível realizar a operação. Tente novamente.',
            ], 500);
        }
    }

    /**
     * Limpa todo o carrinho.
     */
    public function clear(ClearCartAction $action): JsonResponse
    {
        try {
            $action->perform();

            session()->forget('cart');

            return $this->getCartResponse('Carrinho limpo com sucesso.');
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Não foi possível realizar a operação. Tente novamente.',
            ], 500);
        }
    }

    /**
     * Retorna resposta JSON com dados atualizados do carrinho.
     */
    private function getCartResponse(string $message): JsonResponse
    {
        $cart = session('cart', []);

        $total = collect($cart)->sum(fn (array $item) => $item['price'] * $item['quantity']);
        $count = collect($cart)->sum('quantity');

        return response()->json([
            'success' => true,
            'message' => $message,
            'items' => $cart,
            'total' => number_format($total, 2, '.', ''),
            'count' => $count,
        ]);
    }
}
