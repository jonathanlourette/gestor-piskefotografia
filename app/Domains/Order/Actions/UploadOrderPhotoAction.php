<?php

declare(strict_types=1);

namespace App\Domains\Order\Actions;

use App\Domains\Order\OrderItem;
use App\Domains\Order\OrderPhoto;
use App\Integrations\Storage\Contract\StorageServiceInterface;
use App\Support\Action;
use App\Support\Exceptions\BusinessException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class UploadOrderPhotoAction extends Action
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
                /** @var OrderItem $orderItem */
                $orderItem = OrderItem::with(['product', 'order'])->findOrFail($this->data->get('order_item_id'));

                $order = $orderItem->order;

                if ((string) $order->id !== (string) $this->data->get('order_id')) {
                    throw new BusinessException('O item não pertence ao pedido informado.');
                }

                /** @var UploadedFile $file */
                $file = $this->data->get('file');

                $currentPhotosCount = $orderItem->photos()->count();

                if ($currentPhotosCount >= $orderItem->product->photo_limit) {
                    throw new BusinessException('O limite de fotos para este item foi atingido.');
                }

                $originalName = $file->getClientOriginalName();

                $extension = strtolower($file->getClientOriginalExtension());
                if (! in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
                    throw new BusinessException('O arquivo deve ser uma imagem JPG ou PNG.');
                }

                $slugName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
                $randomSuffix = Str::random(8);
                $safeFilename = "{$slugName}-{$randomSuffix}.{$extension}";
                $s3Path = "orders/{$order->id}/{$orderItem->id}/{$safeFilename}";

                $s3Path = $this->storageService->upload(
                    $s3Path,
                    file_get_contents($file->getRealPath()),
                    $file->getMimeType(),
                );

                $photo = new OrderPhoto;
                $photo->order_item_id = $orderItem->id;
                $photo->s3_path = $s3Path;
                $photo->original_name = $originalName;
                $photo->size_bytes = $file->getSize();
                $photo->save();

                return $photo;
            });
        } catch (BusinessException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            throw new BusinessException('Não foi possível realizar o upload da foto. Tente novamente.');
        }
    }

    private function validate(): void
    {
        $this->data->validate([
            'order_id' => ['required', 'integer'],
            'order_item_id' => ['required', 'integer'],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:15360'],
        ], [
            'order_id.required' => 'O ID do pedido é obrigatório.',
            'order_item_id.required' => 'O ID do item é obrigatório.',
            'file.required' => 'O arquivo é obrigatório.',
            'file.mimes' => 'O arquivo deve ser uma imagem JPG ou PNG.',
            'file.max' => 'O arquivo não pode ter mais de 15MB.',
        ]);
    }
}
