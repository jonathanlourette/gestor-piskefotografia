<?php

declare(strict_types=1);

namespace App\Apps\Gallery\Controllers\Public;

use App\Domains\Gallery\Actions\GenerateDownloadZipAction;
use App\Domains\Gallery\Actions\ToggleFavoriteAction;
use App\Domains\Gallery\Gallery;
use App\Domains\Gallery\GalleryPhoto;
use App\Http\Controllers\Controller as BaseController;
use App\Integrations\Storage\Contract\StorageServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GalleryViewController extends BaseController
{
    public function __construct(
        private readonly StorageServiceInterface $storageService
    ) {}

    /**
     * Show the gallery showcase page.
     */
    public function showcase(Request $request, string $uuid): View
    {
        $gallery = $request->attributes->get('gallery');

        $coverUrl = $gallery->cover_photo_path
            ? $this->storageService->getUrl($gallery->cover_photo_path)
            : null;

        return view('gallery::public.showcase', [
            'gallery' => $gallery,
            'coverUrl' => $coverUrl,
        ]);
    }

    /**
     * Get gallery photos for infinite scroll (cursor pagination).
     */
    public function photos(Request $request, string $uuid): JsonResponse
    {
        $gallery = $request->attributes->get('gallery');

        $after = $request->query('cursor', 0);

        $photos = GalleryPhoto::where('gallery_id', $gallery->id)
            ->where('sort_order', '>', (int) $after)
            ->orderBy('sort_order', 'asc')
            ->limit(30)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'thumbnail_url' => $this->storageService->getUrl($p->thumbnail_path),
                'url' => $this->storageService->getUrl($p->s3_path),
                'original_name' => $p->original_name,
                'size_bytes' => $p->size_bytes,
                'sort_order' => $p->sort_order,
                'is_favorited' => false,
            ]);

        $nextCursor = $photos->count() === 30 ? $photos->last()['sort_order'] : null;

        return response()->json([
            'photos' => $photos,
            'next_cursor' => $nextCursor,
        ]);
    }

    /**
     * Get single photo detail (for lightbox).
     */
    public function photo(Request $request, string $uuid, int $photoId): JsonResponse
    {
        $gallery = $request->attributes->get('gallery');

        $photo = GalleryPhoto::where('gallery_id', $gallery->id)
            ->findOrFail($photoId);

        return response()->json([
            'id' => $photo->id,
            'url' => $this->storageService->getUrl($photo->s3_path),
            'original_name' => $photo->original_name,
            'size_bytes' => $photo->size_bytes,
        ]);
    }

    /**
     * Toggle favorite status for a photo.
     */
    public function toggleFavorite(
        Request $request,
        string $uuid,
        int $photoId,
        ToggleFavoriteAction $action
    ): JsonResponse {
        $visitorToken = $request->cookie('gallery_visitor_token', Str::uuid()->toString());

        $result = $action->setData([
            'gallery_photo_id' => $photoId,
            'visitor_token' => $visitorToken,
        ])->perform();

        return response()
            ->json($result)
            ->cookie('gallery_visitor_token', $visitorToken, 60 * 24 * 90);
    }

    /**
     * Download gallery as ZIP.
     */
    public function downloadZip(Request $request, string $uuid, GenerateDownloadZipAction $action)
    {
        $gallery = $request->attributes->get('gallery');

        return $action->setData(['gallery_id' => $gallery->id])->perform();
    }
}
