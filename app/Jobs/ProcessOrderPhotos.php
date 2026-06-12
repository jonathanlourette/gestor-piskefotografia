<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domains\Order\Enums\OrderStatusEnum;
use App\Domains\Order\Order;
use App\Domains\Order\OrderPhoto;
use App\Integrations\Storage\Contract\StorageServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessOrderPhotos implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [30, 60, 120];

    public int $timeout = 600; // 10 minutes max

    public function __construct(
        public readonly int $orderId,
    ) {}

    public function handle(StorageServiceInterface $storageService): void
    {
        $order = Order::with(['items.photos'])->find($this->orderId);

        if ($order === null) {
            Log::error('ProcessOrderPhotos: order not found', ['order_id' => $this->orderId]);

            return;
        }

        // Get all photos that still need processing (have original_s3_path set)
        $photos = $order->items->flatMap->photos->filter(
            fn (OrderPhoto $photo) => $photo->original_s3_path !== null,
        );

        if ($photos->isEmpty()) {
            Log::info('ProcessOrderPhotos: no photos to process', ['order_id' => $this->orderId]);

            // All already processed, just update status
            DB::transaction(function () use ($order): void {
                $lockedOrder = Order::lockForUpdate()->find($order->id);
                if ($lockedOrder->status === OrderStatusEnum::PROCESSANDO) {
                    $lockedOrder->status = OrderStatusEnum::PROCESSADO;
                    $lockedOrder->save();
                }
            });

            return;
        }

        $total = $photos->count();
        $processed = 0;

        foreach ($photos as $photo) {
            $this->processPhoto($photo, $storageService);
            $processed++;

            Log::info('ProcessOrderPhotos: photo processed', [
                'order_id' => $this->orderId,
                'photo_id' => $photo->id,
                'progress' => "{$processed}/{$total}",
            ]);

            // 2-second delay between photos
            if ($processed < $total) {
                sleep(2);
            }
        }

        // All photos processed — update order status atomically
        DB::transaction(function () use ($order): void {
            $lockedOrder = Order::lockForUpdate()->find($order->id);
            if ($lockedOrder->status === OrderStatusEnum::PROCESSANDO) {
                $lockedOrder->status = OrderStatusEnum::PROCESSADO;
                $lockedOrder->save();

                Log::info('ProcessOrderPhotos: order status updated to PROCESSADO', [
                    'order_id' => $lockedOrder->id,
                ]);
            }
        });
    }

    private function processPhoto(OrderPhoto $photo, StorageServiceInterface $storageService): void
    {
        $tempFile = sys_get_temp_dir().'/process_'.Str::random(16).'.jpg';

        try {
            // 1. Read original from S3
            $originalContent = Storage::disk('s3')->get($photo->original_s3_path);

            if ($originalContent === null) {
                Log::error('ProcessOrderPhotos: original file not found in S3', [
                    'photo_id' => $photo->id,
                    'original_s3_path' => $photo->original_s3_path,
                ]);

                return;
            }

            // 2. Write to temp file
            file_put_contents($tempFile, $originalContent);

            // 3. Check image type
            $imageInfo = @getimagesize($tempFile);
            if ($imageInfo === false) {
                Log::error('ProcessOrderPhotos: cannot read image info', ['photo_id' => $photo->id]);

                return;
            }

            // 4. Create image resource
            $sourceImage = match ($imageInfo[2]) {
                IMAGETYPE_JPEG => @imagecreatefromjpeg($tempFile),
                IMAGETYPE_PNG => @imagecreatefrompng($tempFile),
                IMAGETYPE_WEBP => @imagecreatefromwebp($tempFile),
                default => null,
            };

            if ($sourceImage === false || $sourceImage === null) {
                Log::error('ProcessOrderPhotos: cannot create image resource', [
                    'photo_id' => $photo->id,
                    'image_type' => $imageInfo[2],
                ]);

                return;
            }

            // 5. EXIF rotation for JPEGs
            if (function_exists('exif_read_data') && $imageInfo[2] === IMAGETYPE_JPEG) {
                $exif = @exif_read_data($tempFile);
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

            // 6. Resize to max 4000px if needed
            $origW = imagesx($sourceImage);
            $origH = imagesy($sourceImage);
            $maxDimension = 4000;
            $quality = 80;

            if ($origW > $maxDimension || $origH > $maxDimension) {
                if ($origW > $origH) {
                    $newW = $maxDimension;
                    $newH = (int) round(($origH / $origW) * $maxDimension);
                } else {
                    $newH = $maxDimension;
                    $newW = (int) round(($origW / $origH) * $maxDimension);
                }

                $resized = imagecreatetruecolor($newW, $newH);
                imagecopyresampled($resized, $sourceImage, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
                imagedestroy($sourceImage);
                $sourceImage = $resized;
            }

            // 7. Encode to JPEG
            ob_start();
            imagejpeg($sourceImage, quality: $quality);
            $processedContent = ob_get_clean();
            imagedestroy($sourceImage);

            if ($processedContent === false) {
                Log::error('ProcessOrderPhotos: failed to encode JPEG', ['photo_id' => $photo->id]);

                return;
            }

            // 8. Upload processed: derive path by removing "originals/" from path
            $processedPath = str_replace('/originals/', '/', $photo->original_s3_path);
            $processedPath = $storageService->upload($processedPath, $processedContent, 'image/jpeg');

            // 9. Delete original from S3
            Storage::disk('s3')->delete($photo->original_s3_path);

            // 10. Update photo record
            $photo->s3_path = $processedPath;
            $photo->original_s3_path = null;
            $photo->size_bytes = strlen($processedContent);
            $photo->save();
        } catch (\Throwable $e) {
            Log::error('ProcessOrderPhotos: failed to process photo', [
                'photo_id' => $photo->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        } finally {
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }
    }
}
