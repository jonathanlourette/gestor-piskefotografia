<?php

declare(strict_types=1);

namespace App\Apps\Gallery\Controllers\Admin;

use App\Apps\Admin\Controllers\BaseAdminController;
use App\Domains\Gallery\Actions\DeleteGalleryPhotosAction;
use App\Domains\Gallery\Actions\UploadGalleryPhotoAction;
use App\Domains\Gallery\Gallery;
use App\Domains\Gallery\GalleryPhoto;
use App\Integrations\Storage\Contract\StorageServiceInterface;
use App\Support\Exceptions\BusinessException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GalleryPhotoController extends BaseAdminController
{
    public function __construct(
        private readonly StorageServiceInterface $storageService,
    ) {}

    /**
     * Exibe a lista de fotos de uma galeria.
     */
    public function index(int $id): View
    {
        $gallery = Gallery::with(['photos' => fn ($query) => $query->ordered()])->findOrFail($id);

        // Gera URLs temporárias para as fotos
        $gallery->photos->each(function (GalleryPhoto $photo) {
            $photo->temporary_url = $this->storageService->getUrl($photo->s3_path);
        });

        return view('gallery::admin.photos', [
            'gallery' => $gallery,
            'photosJson' => $gallery->photos->map(fn (GalleryPhoto $p) => [
                'id' => $p->id,
                's3_path' => $p->s3_path,
                'thumbnail_path' => $p->thumbnail_path,
                'original_name' => $p->original_name,
                'size_bytes' => $p->size_bytes,
                'sort_order' => $p->sort_order,
                'temporary_url' => $p->temporary_url,
            ])->toJson(),
        ]);
    }

    /**
     * Faz upload de uma nova foto para a galeria (AJAX).
     */
    public function store(Request $request, int $id, UploadGalleryPhotoAction $action): JsonResponse
    {
        try {
            // SEGURANÇA: Usando o valor vindo da rota `$id` do lado esquerdo
            $data = ['gallery_id' => $id] + $request->all();

            $photo = $action->setData($data)->perform();

            $photo->temporary_url = $this->storageService->getUrl($photo->s3_path);

            return response()->json([
                'success' => true,
                'photo' => $photo,
            ]);
        } catch (BusinessException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Não foi possível fazer upload da foto. Tente novamente.',
            ], 500);
        }
    }

    /**
     * Remove fotos da galeria (AJAX).
     */
    public function destroy(Request $request, int $id, DeleteGalleryPhotosAction $action): JsonResponse
    {
        try {
            // SEGURANÇA: Usando o valor vindo da rota `$id` do lado esquerdo
            $data = ['gallery_id' => $id, 'photo_ids' => $request->input('photo_ids', [])];

            $deletedCount = $action->setData($data)->perform();

            return response()->json([
                'success' => true,
                'deleted_count' => $deletedCount,
                'message' => "{$deletedCount} foto(s) removida(s) com sucesso!",
            ]);
        } catch (BusinessException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Não foi possível remover as fotos. Tente novamente.',
            ], 500);
        }
    }

    /**
     * Define uma foto como capa da galeria (AJAX).
     */
    public function setCover(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'photo_id' => ['required', 'integer', 'exists:gallery_photos,id'],
            ], [
                'photo_id.required' => 'O ID da foto é obrigatório.',
                'photo_id.exists' => 'Foto não encontrada.',
            ]);

            $gallery = Gallery::findOrFail($id);
            $photo = GalleryPhoto::findOrFail($request->input('photo_id'));

            if ($photo->gallery_id !== $gallery->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta foto não pertence a esta galeria.',
                ], 422);
            }

            $gallery->cover_photo_path = $photo->s3_path;
            $gallery->save();

            return response()->json([
                'success' => true,
                'message' => 'Capa definida com sucesso!',
            ]);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Não foi possível definir a capa. Tente novamente.',
            ], 500);
        }
    }
}
