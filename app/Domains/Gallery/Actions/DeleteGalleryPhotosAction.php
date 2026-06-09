<?php

declare(strict_types=1);

namespace App\Domains\Gallery\Actions;

use App\Domains\Gallery\Gallery;
use App\Domains\Gallery\GalleryFavorite;
use App\Domains\Gallery\GalleryPhoto;
use App\Integrations\Storage\Contract\StorageServiceInterface;
use App\Support\Action;
use App\Support\Exceptions\BusinessException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

final class DeleteGalleryPhotosAction extends Action
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
                $photoIds = $this->data->get('photo_ids');

                $gallery = Gallery::findOrFail($galleryId);

                $photos = GalleryPhoto::whereIn('id', $photoIds)->get();

                foreach ($photos as $photo) {
                    if ($photo->gallery_id !== $galleryId) {
                        throw new BusinessException('Uma ou mais fotos não pertencem a esta galeria.');
                    }
                }

                $deletedCount = 0;

                foreach ($photos as $photo) {
                    GalleryFavorite::where('gallery_photo_id', $photo->id)->delete();

                    if ($photo->s3_path !== $photo->thumbnail_path) {
                        $this->storageService->delete($photo->thumbnail_path);
                    }
                    $this->storageService->delete($photo->s3_path);

                    $photo->delete();
                    $deletedCount++;
                }

                $gallery->decrement('photos_count', $deletedCount);

                return $deletedCount;
            });
        } catch (ModelNotFoundException $e) {
            throw new BusinessException('Galeria não encontrada.');
        } catch (BusinessException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            throw new BusinessException('Não foi possível excluir as fotos. Tente novamente.');
        }
    }

    private function validate(): void
    {
        $this->data->validate([
            'gallery_id' => ['required', 'integer', 'exists:galleries,id'],
            'photo_ids' => ['required', 'array', 'min:1'],
            'photo_ids.*' => ['integer', 'exists:gallery_photos,id'],
        ], [
            'gallery_id.required' => 'O ID da galeria é obrigatório.',
            'gallery_id.exists' => 'Galeria não encontrada.',
            'photo_ids.required' => 'Selecione ao menos uma foto para excluir.',
            'photo_ids.min' => 'Selecione ao menos uma foto para excluir.',
            'photo_ids.*.exists' => 'Uma ou mais fotos não foram encontradas.',
        ]);
    }
}
