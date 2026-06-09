<?php

declare(strict_types=1);

namespace App\Domains\Gallery\Actions;

use App\Domains\Gallery\Enums\GalleryStatusEnum;
use App\Domains\Gallery\Gallery;
use App\Support\Action;
use App\Support\Exceptions\BusinessException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

final class ActivateGalleryAction extends Action
{
    /**
     * @throws BusinessException
     */
    public function perform(): mixed
    {
        $this->validate();

        try {
            return DB::transaction(function () {
                $gallery = Gallery::findOrFail($this->data->get('id'));

                if ($gallery->status === GalleryStatusEnum::ACTIVE) {
                    throw new BusinessException('Esta galeria já está ativa.');
                }

                if ($gallery->photos_count === 0) {
                    throw new BusinessException('A galeria precisa ter pelo menos uma foto para ser ativada.');
                }

                $gallery->status = GalleryStatusEnum::ACTIVE;

                if ($gallery->cover_photo_path === null) {
                    $firstPhoto = $gallery->photos()->orderBy('sort_order', 'asc')->first();

                    if ($firstPhoto !== null) {
                        $gallery->cover_photo_path = $firstPhoto->s3_path;
                    }
                }

                $gallery->save();

                return $gallery->fresh();
            });
        } catch (ModelNotFoundException $e) {
            throw new BusinessException('Galeria não encontrada.');
        } catch (BusinessException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            throw new BusinessException('Não foi possível ativar a galeria. Tente novamente.');
        }
    }

    private function validate(): void
    {
        $this->data->validate([
            'id' => ['required', 'integer', 'exists:galleries,id'],
        ], [
            'id.required' => 'O ID da galeria é obrigatório.',
            'id.exists' => 'Galeria não encontrada.',
        ]);
    }
}
