<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Gera miniaturas de imagens JPG/PNG usando GD.
 */
final class ThumbnailGenerator
{
    /**
     * Gera uma miniatura com o lado maior limitado a $maxSize pixels.
     *
     * @param  string  $filePath  Caminho do arquivo de imagem original.
     * @param  string  $extension  Extensão da imagem (jpg, jpeg ou png).
     * @param  int  $maxSize  Tamanho máximo (px) do lado maior.
     * @return string|null Conteúdo binário da miniatura, ou null se a imagem
     *                     já for menor que $maxSize ou se a geração falhar.
     */
    public static function generate(string $filePath, string $extension, int $maxSize = 600): ?string
    {
        try {
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

            if ($origW <= $maxSize && $origH <= $maxSize) {
                imagedestroy($sourceImage);

                return null;
            }

            if ($origW > $origH) {
                $newW = $maxSize;
                $newH = (int) round(($origH / $origW) * $maxSize);
            } else {
                $newH = $maxSize;
                $newW = (int) round(($origW / $origH) * $maxSize);
            }

            $thumb = imagecreatetruecolor($newW, $newH);

            if ($extension === 'png') {
                imagealphablending($thumb, false);
                imagesavealpha($thumb, true);
                $transparent = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
                imagefill($thumb, 0, 0, $transparent);
            }

            imagecopyresampled($thumb, $sourceImage, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

            ob_start();
            if ($extension === 'png') {
                imagepng($thumb, quality: 8);
            } else {
                imagejpeg($thumb, quality: 82);
            }
            $content = ob_get_clean();

            imagedestroy($sourceImage);
            imagedestroy($thumb);

            return $content !== false ? $content : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
