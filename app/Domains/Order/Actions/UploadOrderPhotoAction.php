<?php

declare(strict_types=1);

namespace App\Domains\Order\Actions;

use App\Domains\Order\OrderItem;
use App\Domains\Order\OrderPhoto;
use App\Integrations\Storage\Contract\StorageServiceInterface;
use App\Support\Action;
use App\Support\Exceptions\BusinessException;
use App\Support\ThumbnailGenerator;
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

                if ($currentPhotosCount >= $orderItem->photoLimit()) {
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
                $thumbnailPath = "orders/{$order->id}/{$orderItem->id}/thumbs/{$safeFilename}";

                $s3Path = $this->storageService->upload(
                    $s3Path,
                    file_get_contents($file->getRealPath()),
                    $file->getMimeType(),
                );

                /** @var UploadedFile|null $clientThumbnail */
                $clientThumbnail = $this->data->get('thumbnail');

                if ($clientThumbnail instanceof UploadedFile) {
                    // Miniatura gerada no navegador: evita o custo do GD no servidor
                    $thumbnailPath = "orders/{$order->id}/{$orderItem->id}/thumbs/{$slugName}-{$randomSuffix}.jpg";
                    $thumbnailPath = $this->storageService->upload(
                        $thumbnailPath,
                        file_get_contents($clientThumbnail->getRealPath()),
                        'image/jpeg',
                    );
                } else {
                    $thumbnailContent = ThumbnailGenerator::generate($file->getRealPath(), $extension);

                    if ($thumbnailContent !== null) {
                        $thumbnailPath = $this->storageService->upload($thumbnailPath, $thumbnailContent, $file->getMimeType());
                    } else {
                        // Imagem pequena ou falha do GD: usa a original como miniatura
                        $thumbnailPath = $s3Path;
                    }
                }

                $photo = new OrderPhoto;
                $photo->order_item_id = $orderItem->id;
                $photo->s3_path = $s3Path;
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
            throw new BusinessException('Não foi possível realizar o upload da foto. Tente novamente.');
        }
    }

    private function validate(): void
    {
        $this->data->validate([
            'order_id' => ['required', 'integer'],
            'order_item_id' => ['required', 'integer'],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:15360'],
            'thumbnail' => ['nullable', 'file', 'mimes:jpg,jpeg', 'max:2048'],
        ], [
            'order_id.required' => 'O ID do pedido é obrigatório.',
            'order_item_id.required' => 'O ID do item é obrigatório.',
            'file.required' => 'O arquivo é obrigatório.',
            'file.mimes' => 'O arquivo deve ser uma imagem JPG ou PNG.',
            'file.max' => 'O arquivo não pode ter mais de 15MB.',
        ]);
    }
}
