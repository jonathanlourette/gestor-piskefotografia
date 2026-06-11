<?php

declare(strict_types=1);

namespace App\Domains\Order\Actions;

use App\Domains\Order\Order;
use App\Integrations\Storage\Contract\StorageServiceInterface;
use App\Support\Action;
use App\Support\Exceptions\BusinessException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class DeleteOrderAction extends Action
{
    public function __construct(
        private readonly StorageServiceInterface $storageService,
    ) {}

    /**
     * @throws BusinessException
     */
    public function perform(): mixed
    {
        $this->validate();

        try {
            return DB::transaction(function () {
                /** @var Order $order */
                $order = Order::with('items.photos')->findOrFail($this->data->get('id'));

                // Deletar todas as fotos do S3
                foreach ($order->items as $item) {
                    foreach ($item->photos as $photo) {
                        // Deletar thumbnail se existir e for diferente da original
                        if ($photo->thumbnail_path && $photo->thumbnail_path !== $photo->s3_path) {
                            try {
                                $this->storageService->delete($photo->thumbnail_path);
                            } catch (\Throwable $e) {
                                Log::warning("Erro ao deletar thumbnail {$photo->thumbnail_path}: {$e->getMessage()}");
                            }
                        }

                        // Deletar arquivo original
                        try {
                            $this->storageService->delete($photo->s3_path);
                        } catch (\Throwable $e) {
                            Log::warning("Erro ao deletar arquivo {$photo->s3_path}: {$e->getMessage()}");
                        }
                    }
                }

                // Deletar o order (cascade cuida de items e photos no banco)
                $order->delete();

                return true;
            });
        } catch (ModelNotFoundException $e) {
            throw new BusinessException('Pedido não encontrado.');
        } catch (\Throwable $e) {
            report($e);
            throw new BusinessException('Não foi possível excluir o pedido. Tente novamente.');
        }
    }

    private function validate(): void
    {
        $this->data->validate([
            'id' => ['required', 'integer', 'exists:orders,id'],
        ], [
            'id.required' => 'O ID do pedido é obrigatório.',
            'id.exists' => 'Pedido não encontrado.',
        ]);
    }
}
