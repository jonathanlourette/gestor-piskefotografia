<?php

declare(strict_types=1);

namespace App\Apps\Admin\Controllers;

use App\Domains\Order\Actions\DeleteOrderAction;
use App\Domains\Order\Actions\RetrieveOrderAction;
use App\Domains\Order\Actions\RetrieveOrdersAction;
use App\Domains\Order\Actions\UpdateOrderNotesAction;
use App\Domains\Order\Actions\UpdateOrderStatusAction;
use App\Domains\Order\Enums\OrderStatusEnum;
use App\Domains\Order\Order;
use App\Integrations\Storage\Contract\StorageServiceInterface;
use App\Support\Exceptions\BusinessException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends BaseAdminController
{
    /**
     * Lista os pedidos com filtros de status e busca.
     */
    public function index(Request $request, RetrieveOrdersAction $action): View
    {
        $data = [
            'status' => $request->query('status'),
            'search' => $request->query('search'),
            'per_page' => $request->query('per_page', 15),
        ];

        $orders = $action->setData($data)->perform();

        return view('admin::orders.index', [
            'orders' => $orders,
            'filters' => $data,
        ]);
    }

    /**
     * Exibe os detalhes de um pedido específico.
     */
    public function show(int $id, RetrieveOrderAction $action, StorageServiceInterface $storageService): View
    {
        /** @var Order $order */
        $order = $action->setData(['id' => $id])->perform();

        // Show processing screen for orders awaiting photo upload or processing
        if (in_array($order->status, [OrderStatusEnum::ENVIADO, OrderStatusEnum::PROCESSANDO], true)) {
            return view('admin::orders.processing', [
                'order' => $order,
            ]);
        }

        $order->items->each(function ($item) use ($storageService) {
            $item->photos->each(function ($photo) use ($storageService) {
                $photo->temporary_url = $storageService->getUrl($photo->s3_path);
            });
        });

        return view('admin::orders.show', [
            'order' => $order,
            'statusOptions' => OrderStatusEnum::adminOptions(),
        ]);
    }

    /**
     * Atualiza o status de um pedido.
     */
    public function updateStatus(Request $request, int $id, UpdateOrderStatusAction $action): RedirectResponse
    {
        try {
            $action->setData([
                'order_id' => $id,
                'status' => $request->input('status'),
            ])->perform();

            return back()->with('success', 'Status atualizado com sucesso!');
        } catch (\Exception $e) {
            return back()->with('warning', $e->getMessage());
        }
    }

    /**
     * Exibe a galeria de fotos de um pedido.
     */
    public function photos(int $id, RetrieveOrderAction $action, StorageServiceInterface $storageService): View
    {
        /** @var Order $order */
        $order = $action->setData(['id' => $id])->perform();

        $order->items->each(function ($item) use ($storageService) {
            $item->photos->each(function ($photo) use ($storageService) {
                $photo->temporary_url = $storageService->getUrl($photo->s3_path);
            });
        });

        return view('admin::orders.photos', [
            'order' => $order,
        ]);
    }

    /**
     * Atualiza as notas internas do pedido.
     */
    public function updateNotes(Request $request, int $id, UpdateOrderNotesAction $action): RedirectResponse
    {
        try {
            $action->setData([
                'id' => $id,
                'notes' => $request->input('notes'),
            ])->perform();

            return back()->with('success', 'Notas internas atualizadas com sucesso!');
        } catch (\Exception $e) {
            return back()->with('warning', $e->getMessage());
        }
    }

    /**
     * Exclui um pedido e todas as suas fotos do S3.
     */
    public function delete(int $id, DeleteOrderAction $action): RedirectResponse
    {
        try {
            $action->setData(['id' => $id])->perform();

            return redirect()
                ->route('admin.orders.index')
                ->with('success', 'Pedido excluído com sucesso!');
        } catch (BusinessException $e) {
            return back()->with('warning', $e->getMessage());
        }
    }
}
