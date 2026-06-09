<?php

declare(strict_types=1);

namespace App\Domains\Gallery\Actions;

use App\Domains\Gallery\Gallery;
use App\Domains\Gallery\GalleryPhoto;
use App\Integrations\Storage\Contract\StorageServiceInterface;
use App\Support\Action;
use App\Support\Exceptions\BusinessException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class UploadGalleryPhotoAction extends Action
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
                $galleryId = (int) $this->data->get('gallery_id');
                $file = $this->data->get('file');

                $gallery = Gallery::findOrFail($galleryId);

                $maxSortOrder = GalleryPhoto::where('gallery_id', $galleryId)->max('sort_order') ?? 0;
                $sortOrder = $maxSortOrder + 1;

                $timestamp = now()->format('YmdHis');
                $originalName = $file->getClientOriginalName();
                $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
                $safeName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)).'_'.Str::random(8);
                $filename = $safeName.'.'.$extension;

                $s3Path = "galleries/{$galleryId}/originals/{$timestamp}_{$filename}";
                $thumbPath = "galleries/{$galleryId}/thumbnails/{$timestamp}_{$filename}";

                $fileContent = $file->get();
                $this->storageService->upload($s3Path, $fileContent, $file->getMimeType());

                // Generate thumbnail using GD (max 1000px on longest side)
                $thumbContent = $this->generateThumbnail($file->getRealPath(), $extension);
                if ($thumbContent !== null) {
                    $this->storageService->upload($thumbPath, $thumbContent, $file->getMimeType());
                } else {
                    // Fallback: if GD fails, use original as thumbnail
                    $thumbPath = $s3Path;
                }

                $photo = new GalleryPhoto;
                $photo->gallery_id = $galleryId;
                $photo->s3_path = $s3Path;
                $photo->thumbnail_path = $thumbPath;
                $photo->original_name = $originalName;
                $photo->size_bytes = $file->getSize();
                $photo->sort_order = $sortOrder;
                $photo->save();

                $gallery->increment('photos_count');

                return $photo;
            });
        } catch (ModelNotFoundException $e) {
            throw new BusinessException('Galeria não encontrada.');
        } catch (BusinessException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            throw new BusinessException('Não foi possível fazer upload da foto. Tente novamente.');
        }
    }

    private function validate(): void
    {
        $this->data->validate([
            'gallery_id' => ['required', 'integer', 'exists:galleries,id'],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:15360'],
        ], [
            'gallery_id.required' => 'O ID da galeria é obrigatório.',
            'gallery_id.exists' => 'Galeria não encontrada.',
            'file.required' => 'O arquivo é obrigatório.',
            'file.mimes' => 'Formato de arquivo inválido. Use JPG, JPEG ou PNG.',
            'file.max' => 'O arquivo não pode ser maior que 15MB.',
        ]);

        // Validação adicional do MIME type real (magic bytes)
        $file = $this->data->get('file');
        if ($file) {
            $mimeType = $file->getMimeType();
            $allowedMimeTypes = ['image/jpeg', 'image/png'];
            if (! in_array($mimeType, $allowedMimeTypes, true)) {
                throw new BusinessException('Tipo de arquivo inválido. Use JPG ou PNG.');
            }
        }
    }

    /**
     * Generate a thumbnail image (max 1000px on longest side) using GD.
     *
     * @return string|null Binary content of the thumbnail, or null on failure.
     */
    private function generateThumbnail(string $filePath, string $extension): ?string
    {
        try {
            $maxSize = 1000;

            $sourceImage = match ($extension) {
                'png' => imagecreatefrompng($filePath),
                'jpg', 'jpeg' => imagecreatefromjpeg($filePath),
                default => null,
            };

            if ($sourceImage === false || $sourceImage === null) {
                return null;
            }

            $origW = imagesx($sourceImage);
            $origH = imagesy($sourceImage);

            // Only resize if larger than maxSize
            if ($origW <= $maxSize && $origH <= $maxSize) {
                imagedestroy($sourceImage);

                return null; // Use original as thumbnail
            }

            if ($origW > $origH) {
                $newW = $maxSize;
                $newH = (int) round(($origH / $origW) * $maxSize);
            } else {
                $newH = $maxSize;
                $newW = (int) round(($origW / $origH) * $maxSize);
            }

            $thumb = imagecreatetruecolor($newW, $newH);

            // Preserve transparency for PNG
            if ($extension === 'png') {
                imagealphablending($thumb, false);
                imagesavealpha($thumb, true);
                $transparent = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
                imagefill($thumb, 0, 0, $transparent);
            }

            imagecopyresampled($thumb, $sourceImage, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

            // Capture output to string
            ob_start();
            if ($extension === 'png') {
                imagepng($thumb, quality: 8);
            } else {
                imagejpeg($thumb, quality: 85);
            }
            $content = ob_get_clean();

            imagedestroy($sourceImage);
            imagedestroy($thumb);

            return $content;
        } catch (\Throwable) {
            return null;
        }
    }
}
