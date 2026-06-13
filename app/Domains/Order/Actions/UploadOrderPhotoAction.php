<?php

declare(strict_types=1);

namespace App\Domains\Order\Actions;

use App\Domains\Order\OrderItem;
use App\Domains\Order\OrderPhoto;
use App\Support\Action;
use App\Support\Exceptions\BusinessException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class UploadOrderPhotoAction extends Action
{
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

                if ($currentPhotosCount >= $orderItem->photoLimit()) {
                    throw new BusinessException('O limite de fotos para este item foi atingido.');
                }

                $originalName = $file->getClientOriginalName();
                $mimeType = $file->getMimeType();

                $slugName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
                $randomSuffix = Str::random(8);
                $safeFilename = "{$slugName}-{$randomSuffix}.jpg";

                // 1. Save original to public disk — instant, no network
                $originalPath = "orders/{$order->id}/{$orderItem->id}/originals/{$safeFilename}";
                $originalContent = file_get_contents($file->getRealPath());
                Storage::disk('public')->put($originalPath, $originalContent);

                // 2. Save thumbnail to public disk (from frontend — no GD processing on server)
                $thumbnailPath = "orders/{$order->id}/{$orderItem->id}/thumbs/{$safeFilename}";
                /** @var UploadedFile|null $thumbnailFile */
                $thumbnailFile = $this->data->get('thumbnail');

                if ($thumbnailFile !== null) {
                    $thumbnailContent = file_get_contents($thumbnailFile->getRealPath());
                    Storage::disk('public')->put($thumbnailPath, $thumbnailContent);
                } else {
                    $thumbnailPath = $originalPath;
                }

                // 3. Create OrderPhoto with local disk reference
                $photo = new OrderPhoto;
                $photo->order_item_id = $orderItem->id;
                $photo->storage_disk = 'local';
                $photo->s3_path = $originalPath;
                $photo->original_s3_path = $originalPath;
                $photo->thumbnail_path = $thumbnailPath;
                $photo->original_name = $originalName;
                $photo->size_bytes = $file->getSize();
                $photo->save();

                return $photo;
            });
        } catch (BusinessException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            Log::error('UploadOrderPhotoAction: upload failed', [
                'order_id' => $this->data->get('order_id'),
                'order_item_id' => $this->data->get('order_item_id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new BusinessException('Não foi possível realizar o upload da foto. Tente novamente.');
        }
    }

    private function validate(): void
    {
        $this->data->validate([
            'order_id' => ['required', 'integer'],
            'order_item_id' => ['required', 'integer'],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:20480'],
            'thumbnail' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'order_id.required' => 'O ID do pedido é obrigatório.',
            'order_item_id.required' => 'O ID do item é obrigatório.',
            'file.required' => 'O arquivo é obrigatório.',
            'file.mimes' => 'O arquivo deve ser uma imagem JPG, PNG ou WebP.',
            'file.max' => 'O arquivo não pode ter mais de 20MB.',
        ]);
    }
}
