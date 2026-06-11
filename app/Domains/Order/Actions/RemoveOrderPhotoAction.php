<?php

declare(strict_types=1);

namespace App\Domains\Order\Actions;

use App\Domains\Order\OrderPhoto;
use App\Integrations\Storage\Contract\StorageServiceInterface;
use App\Support\Action;
use App\Support\Exceptions\BusinessException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

final class RemoveOrderPhotoAction extends Action
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
                /** @var OrderPhoto $photo */
                $photo = OrderPhoto::with('orderItem')->findOrFail($this->data->get('photo_id'));

                if ((string) $photo->orderItem->order_id !== (string) $this->data->get('order_id')) {
                    throw new BusinessException('A foto não pertence ao pedido informado.');
                }

                if ($photo->thumbnail_path && $photo->thumbnail_path !== $photo->s3_path) {
                    $this->storageService->delete($photo->thumbnail_path);
                }
                $this->storageService->delete($photo->s3_path);

                $photo->delete();

                return true;
            });
        } catch (ModelNotFoundException $e) {
            throw new BusinessException('Foto não encontrada.');
        } catch (BusinessException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            throw new BusinessException('Não foi possível remover a foto. Tente novamente.');
        }
    }

    private function validate(): void
    {
        $this->data->validate([
            'order_id' => ['required', 'integer'],
            'photo_id' => ['required', 'integer', 'exists:order_photos,id'],
        ], [
            'order_id.required' => 'O ID do pedido é obrigatório.',
            'photo_id.required' => 'O ID da foto é obrigatório.',
            'photo_id.exists' => 'Foto não encontrada.',
        ]);
    }
}
