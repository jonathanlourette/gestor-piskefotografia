<?php

declare(strict_types=1);

namespace App\Domains\Gallery\Actions;

use App\Domains\Gallery\GalleryFavorite;
use App\Domains\Gallery\GalleryPhoto;
use App\Support\Action;
use App\Support\Exceptions\BusinessException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class ToggleFavoriteAction extends Action
{
    /**
     * @throws BusinessException
     */
    public function perform(): mixed
    {
        $this->validate();

        try {
            $photo = GalleryPhoto::findOrFail($this->data->get('gallery_photo_id'));
            $visitorToken = $this->data->get('visitor_token');

            $favorite = GalleryFavorite::firstOrNew([
                'gallery_photo_id' => $photo->id,
                'visitor_token' => $visitorToken,
            ]);

            if ($favorite->exists) {
                $favorite->delete();

                return ['favorited' => false];
            }

            $favorite->save();

            return ['favorited' => true];
        } catch (ModelNotFoundException $e) {
            throw new BusinessException('Foto não encontrada.');
        } catch (BusinessException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            throw new BusinessException('Não foi possível alternar o favorito. Tente novamente.');
        }
    }

    private function validate(): void
    {
        $this->data->validate([
            'gallery_photo_id' => ['required', 'integer', 'exists:gallery_photos,id'],
            'visitor_token' => ['required', 'string', 'uuid'],
        ], [
            'gallery_photo_id.required' => 'O ID da foto é obrigatório.',
            'gallery_photo_id.exists' => 'Foto não encontrada.',
            'visitor_token.required' => 'O token do visitante é obrigatório.',
            'visitor_token.uuid' => 'O token do visitante deve ser um UUID válido.',
        ]);
    }
}
