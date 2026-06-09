<?php

declare(strict_types=1);

namespace App\Domains\Gallery\Actions;

use App\Domains\Gallery\Enums\GalleryStatusEnum;
use App\Domains\Gallery\Gallery;
use App\Support\Action;
use App\Support\Exceptions\BusinessException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipStream\ZipStream;

final class GenerateDownloadZipAction extends Action
{
    /**
     * @throws BusinessException
     */
    public function perform(): mixed
    {
        $this->validate();

        try {
            $gallery = Gallery::with(['photos' => fn ($q) => $q->orderBy('sort_order')])
                ->findOrFail($this->data->get('gallery_id'));

            if ($gallery->status !== GalleryStatusEnum::ACTIVE) {
                throw new BusinessException('Esta galeria não está disponível para download.');
            }

            if ($gallery->photos->isEmpty()) {
                throw new BusinessException('Esta galeria não possui fotos.');
            }

            $safeTitle = Str::slug($gallery->title);

            return response()->streamDownload(function () use ($gallery) {
                $zip = new ZipStream(
                    outputStream: fopen('php://output', 'w'),
                    sendHttpHeaders: false,
                );

                foreach ($gallery->photos as $photo) {
                    $stream = Storage::disk('s3')->readStream($photo->s3_path);

                    if ($stream !== false) {
                        $zip->addFileFromStream(
                            fileName: $photo->original_name,
                            stream: $stream,
                        );

                        if (is_resource($stream)) {
                            fclose($stream);
                        }
                    }
                }

                $zip->finish();
            }, "{$safeTitle}.zip", [
                'Content-Type' => 'application/zip',
            ]);
        } catch (BusinessException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            throw new BusinessException('Não foi possível gerar o ZIP para download. Tente novamente.');
        }
    }

    private function validate(): void
    {
        $this->data->validate([
            'gallery_id' => ['required', 'integer', 'exists:galleries,id'],
        ], [
            'gallery_id.required' => 'O ID da galeria é obrigatório.',
            'gallery_id.integer' => 'O ID da galeria deve ser um número inteiro.',
            'gallery_id.exists' => 'A galeria informada não existe.',
        ]);
    }
}
