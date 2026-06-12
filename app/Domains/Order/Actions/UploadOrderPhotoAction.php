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

                if ($currentPhotosCount >= $orderItem->photoLimit()) {
                    throw new BusinessException('O limite de fotos para este item foi atingido.');
                }

                $originalName = $file->getClientOriginalName();

                // S3 paths — always .jpg since we convert everything to JPEG
                $slugName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
                $randomSuffix = Str::random(8);
                $safeFilename = "{$slugName}-{$randomSuffix}.jpg";
                $s3Path = "orders/{$order->id}/{$orderItem->id}/{$safeFilename}";
                $thumbnailPath = "orders/{$order->id}/{$orderItem->id}/thumbs/{$safeFilename}";

                // Resize original to max 5000px, JPEG 80% quality
                $resizedContent = $this->resizeOriginal($file->getRealPath());

                // Upload resized original to S3
                $s3Path = $this->storageService->upload(
                    $s3Path,
                    $resizedContent,
                    'image/jpeg',
                );

                // Generate thumbnail (350px, JPEG 50%) from the resized image via temp file
                $tempFile = sys_get_temp_dir().'/resized_'.Str::random(16).'.jpg';
                file_put_contents($tempFile, $resizedContent);

                try {
                    $thumbnailContent = $this->generateThumbnail($tempFile, 350, 50);

                    if ($thumbnailContent !== null) {
                        // Upload thumbnail to S3
                        $thumbnailPath = $this->storageService->upload(
                            $thumbnailPath,
                            $thumbnailContent,
                            'image/jpeg',
                        );
                    } else {
                        // Fallback: use resized original as thumbnail
                        $thumbnailPath = $s3Path;
                    }
                } finally {
                    @unlink($tempFile);
                }

                // Create OrderPhoto record
                $photo = new OrderPhoto;
                $photo->order_item_id = $orderItem->id;
                $photo->s3_path = $s3Path;
                $photo->thumbnail_path = $thumbnailPath;
                $photo->original_name = $originalName;
                $photo->size_bytes = strlen($resizedContent);
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
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:20480'],
        ], [
            'order_id.required' => 'O ID do pedido é obrigatório.',
            'order_item_id.required' => 'O ID do item é obrigatório.',
            'file.required' => 'O arquivo é obrigatório.',
            'file.mimes' => 'O arquivo deve ser uma imagem JPG, PNG ou WebP.',
            'file.max' => 'O arquivo não pode ter mais de 20MB.',
        ]);
    }

    /**
     * Redimensiona a imagem original para no máximo $maxDimension pixels no lado maior.
     * Sempre retorna conteúdo binário JPEG com a qualidade especificada.
     *
     * @throws BusinessException
     */
    private function resizeOriginal(string $filePath, int $maxDimension = 5000, int $quality = 80): string
    {
        $imageInfo = @getimagesize($filePath);

        if ($imageInfo === false) {
            throw new BusinessException('Não foi possível processar a imagem.');
        }

        $sourceImage = match ($imageInfo[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($filePath),
            IMAGETYPE_PNG => @imagecreatefrompng($filePath),
            IMAGETYPE_WEBP => @imagecreatefromwebp($filePath),
            default => throw new BusinessException('Formato de imagem não suportado para processamento.'),
        };

        if ($sourceImage === false) {
            throw new BusinessException('Não foi possível processar a imagem.');
        }

        // Corrige orientação EXIF (fotos de celular em portrait)
        if (function_exists('exif_read_data') && $imageInfo[2] === IMAGETYPE_JPEG) {
            $exif = @exif_read_data($filePath);
            if (! empty($exif['Orientation'])) {
                $rotated = match ((int) $exif['Orientation']) {
                    3 => imagerotate($sourceImage, 180, 0),
                    6 => imagerotate($sourceImage, -90, 0),
                    8 => imagerotate($sourceImage, 90, 0),
                    default => $sourceImage,
                };
                if ($rotated !== false && $rotated !== $sourceImage) {
                    imagedestroy($sourceImage);
                    $sourceImage = $rotated;
                }
            }
        }

        $origW = imagesx($sourceImage);
        $origH = imagesy($sourceImage);

        // Image already within limits — still convert to JPEG
        if ($origW <= $maxDimension && $origH <= $maxDimension) {
            ob_start();
            imagejpeg($sourceImage, quality: $quality);
            $content = ob_get_clean();
            imagedestroy($sourceImage);

            if ($content === false) {
                throw new BusinessException('Não foi possível processar a imagem.');
            }

            return $content;
        }

        if ($origW > $origH) {
            $newW = $maxDimension;
            $newH = (int) round(($origH / $origW) * $maxDimension);
        } else {
            $newH = $maxDimension;
            $newW = (int) round(($origW / $origH) * $maxDimension);
        }

        $resized = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($resized, $sourceImage, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        ob_start();
        imagejpeg($resized, quality: $quality);
        $content = ob_get_clean();

        imagedestroy($sourceImage);
        imagedestroy($resized);

        if ($content === false) {
            throw new BusinessException('Não foi possível processar a imagem.');
        }

        return $content;
    }

    /**
     * Gera uma miniatura JPEG a partir de um arquivo de imagem.
     * Sempre retorna conteúdo binário JPEG ou null se a geração falhar.
     */
    private function generateThumbnail(string $filePath, int $maxSize = 350, int $quality = 50): ?string
    {
        try {
            $imageInfo = @getimagesize($filePath);

            if ($imageInfo === false) {
                return null;
            }

            $sourceImage = match ($imageInfo[2]) {
                IMAGETYPE_JPEG => @imagecreatefromjpeg($filePath),
                IMAGETYPE_PNG => @imagecreatefrompng($filePath),
                IMAGETYPE_WEBP => @imagecreatefromwebp($filePath),
                default => null,
            };

            if ($sourceImage === false || $sourceImage === null) {
                return null;
            }

            $origW = imagesx($sourceImage);
            $origH = imagesy($sourceImage);

            if ($origW <= $maxSize && $origH <= $maxSize) {
                ob_start();
                imagejpeg($sourceImage, quality: $quality);
                $content = ob_get_clean();
                imagedestroy($sourceImage);

                return $content !== false ? $content : null;
            }

            if ($origW > $origH) {
                $newW = $maxSize;
                $newH = (int) round(($origH / $origW) * $maxSize);
            } else {
                $newH = $maxSize;
                $newW = (int) round(($origW / $origH) * $maxSize);
            }

            $thumb = imagecreatetruecolor($newW, $newH);
            imagecopyresampled($thumb, $sourceImage, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

            ob_start();
            imagejpeg($thumb, quality: $quality);
            $content = ob_get_clean();

            imagedestroy($sourceImage);
            imagedestroy($thumb);

            return $content !== false ? $content : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
