<?php

declare(strict_types=1);

namespace App\Apps\Site\Controllers;

use App\Domains\Order\Actions\CreateOrderAction;
use App\Domains\Order\Actions\RetrieveOrderAction;
use App\Domains\Order\Actions\UploadOrderPhotoAction;
use App\Support\Exceptions\BusinessException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends BaseSiteController
{
    /**
     * Exibe o formulário de dados do cliente com resumo do carrinho.
     */
    public function create(): View|RedirectResponse
    {
        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()
                ->route('site.landing.index')
                ->with('warning', 'Seu carrinho está vazio. Adicione produtos antes de finalizar.');
        }

        $total = collect($cart)->sum(fn (array $item) => $item['price'] * $item['quantity']);

        return view('site::order.create', [
            'cartItems' => $cart,
            'total' => $total,
        ]);
    }

    /**
     * Cria o pedido a partir dos dados do cliente e itens do carrinho.
     */
    public function store(Request $request, CreateOrderAction $action): RedirectResponse
    {
        try {
            $request->validate([
                'customer_name' => ['required', 'string', 'max:255'],
                'customer_phone' => ['required', 'celular_com_ddd'],
            ], [
                'customer_name.required' => 'O nome é obrigatório.',
                'customer_phone.required' => 'O telefone é obrigatório.',
                'customer_phone.celular_com_ddd' => 'Informe um telefone válido com DDD. Ex: (27) 99999-9999',
            ]);

            $cartItems = session('cart', []);

            if (empty($cartItems)) {
                return back()->with('error', 'Seu carrinho está vazio.');
            }

            $order = $action->setData([
                'customer_name' => $request->input('customer_name'),
                'customer_phone' => $request->input('customer_phone'),
                'items' => $cartItems,
            ])->perform();

            session()->forget('cart');
            session(['pending_order_id' => $order->id]);

            return redirect()
                ->route('site.order.upload', ['id' => $order->id])
                ->with('success', 'Pedido criado com sucesso!');

        } catch (BusinessException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Não foi possível criar o pedido. Tente novamente.');
        }
    }

    /**
     * Exibe a tela de upload de fotos para o pedido.
     */
    public function upload(int $id, RetrieveOrderAction $action): View
    {
        $pendingOrderId = session('pending_order_id');

        if ((int) $pendingOrderId !== $id) {
            abort(403, 'Acesso não autorizado a este pedido.');
        }

        $order = $action->setData(['id' => $id])->perform();

        return view('site::order.upload', [
            'order' => $order,
        ]);
    }

    /**
     * Realiza o upload de uma foto via AJAX.
     */
    public function uploadPhoto(Request $request, int $id, UploadOrderPhotoAction $action): JsonResponse
    {
        try {
            $pendingOrderId = session('pending_order_id');

            if ((int) $pendingOrderId !== $id) {
                abort(403, 'Acesso não autorizado a este pedido.');
            }

            $request->validate([
                'order_item_id' => ['required', 'integer'],
                'photo' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:15360'],
            ], [
                'order_item_id.required' => 'O item do pedido é obrigatório.',
                'photo.required' => 'A foto é obrigatória.',
                'photo.mimes' => 'A foto deve ser JPG ou PNG.',
                'photo.max' => 'A foto não pode ter mais de 15MB.',
            ]);

            $photo = $action->setData([
                'order_id' => $id,
                'order_item_id' => $request->input('order_item_id'),
                'file' => $request->file('photo'),
            ])->perform();

            return response()->json([
                'success' => true,
                'message' => 'Foto enviada com sucesso!',
                'photo' => [
                    'id' => $photo->id,
                    's3_path' => $photo->s3_path,
                    'original_name' => $photo->original_name,
                ],
            ]);

        } catch (BusinessException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Não foi possível enviar a foto. Tente novamente.',
            ], 500);
        }
    }

    /**
     * Finaliza o envio de fotos do pedido.
     */
    public function finalize(int $id, RetrieveOrderAction $action): RedirectResponse
    {
        try {
            $order = $action->setData(['id' => $id])->perform();

            $incompleteItems = $order->items->filter(function ($item) {
                return $item->photos->count() < $item->product->photo_limit;
            });

            if ($incompleteItems->isNotEmpty()) {
                $names = $incompleteItems->map(fn ($item) => $item->product->name)->join(', ');

                return back()->with('warning', "Envie todas as fotos antes de finalizar. Faltam fotos em: {$names}");
            }

            return redirect()
                ->route('site.order.confirmation', ['id' => $id]);

        } catch (BusinessException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Não foi possível finalizar o pedido. Tente novamente.');
        }
    }

    /**
     * Exibe a tela de confirmação do pedido.
     */
    public function confirmation(int $id, RetrieveOrderAction $action): View
    {
        $pendingOrderId = session('pending_order_id');

        if ((int) $pendingOrderId !== $id) {
            abort(403, 'Acesso não autorizado.');
        }

        session()->forget('pending_order_id');

        $order = $action->setData(['id' => $id])->perform();

        $trackingUrl = route('site.tracking.show', $order->uuid);

        return view('site::order.confirmation', [
            'order' => $order,
            'trackingUrl' => $trackingUrl,
        ]);
    }
}
