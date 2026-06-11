<?php

declare(strict_types=1);

namespace App\Integrations\Storage\Services;

use App\Integrations\Storage\Base\StorageService;
use App\Integrations\Storage\Exceptions\StorageException;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Implementação de storage utilizando Amazon S3.
 */
class S3StorageService extends StorageService
{
    /**
     * Faz upload de um arquivo para o S3.
     *
     * @param  string  $path  Caminho de destino no bucket.
     * @param  mixed  $fileContent  Conteúdo do arquivo.
     * @param  string  $contentType  Tipo MIME do arquivo.
     * @return string O caminho do arquivo armazenado.
     *
     * @throws StorageException
     */
    public function upload(string $path, mixed $fileContent, string $contentType = 'image/jpeg'): string
    {
        $options = [
            'ContentType' => $contentType,
            'visibility' => 'private',
        ];

        $contentSize = is_string($fileContent) ? strlen($fileContent) : 0;
        $maxRetries = 3;
        $lastError = null;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $start = microtime(true);
                $result = Storage::disk('s3')->put($path, $fileContent, $options);
                $elapsed = round(microtime(true) - $start, 2);

                if ($result) {
                    logger()->debug('S3 upload success', [
                        'path' => $path,
                        'size_kb' => round($contentSize / 1024),
                        'attempt' => $attempt,
                        'elapsed_s' => $elapsed,
                    ]);

                    return $path;
                }

                $lastError = new StorageException('S3 put returned false.');
                logger()->warning('S3 put returned false', [
                    'path' => $path,
                    'size_kb' => round($contentSize / 1024),
                    'attempt' => $attempt,
                    'elapsed_s' => $elapsed,
                ]);
            } catch (Throwable $e) {
                $lastError = $e;
                logger()->error('S3 upload attempt failed', [
                    'path' => $path,
                    'size_kb' => round($contentSize / 1024),
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                    'class' => $e::class,
                ]);
            }

            if ($attempt < $maxRetries) {
                usleep(500000 * $attempt);
            }
        }

        throw new StorageException('Não foi possível realizar o upload do arquivo. Tente novamente.', 0, $lastError);
    }

    /**
     * Retorna a URL temporária de acesso ao arquivo no S3.
     * Usa URL assinada com host público (para acesso do browser).
     *
     * @param  string  $path  Caminho do arquivo no bucket.
     * @return string URL de acesso.
     *
     * @throws StorageException
     */
    public function getUrl(string $path): string
    {
        try {
            return Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(60));
        } catch (Throwable $e) {
            throw new StorageException('Não foi possível obter a URL do arquivo. Tente novamente.', 0, $e);
        }
    }

    /**
     * Remove um arquivo do S3.
     *
     * @param  string  $path  Caminho do arquivo no bucket.
     * @return bool True se removido com sucesso.
     *
     * @throws StorageException
     */
    public function delete(string $path): bool
    {
        try {
            return Storage::disk('s3')->delete($path);
        } catch (Throwable $e) {
            throw new StorageException('Não foi possível remover o arquivo. Tente novamente.', 0, $e);
        }
    }

    /**
     * Verifica se um arquivo existe no S3.
     *
     * @param  string  $path  Caminho do arquivo no bucket.
     * @return bool True se o arquivo existe.
     *
     * @throws StorageException
     */
    public function exists(string $path): bool
    {
        try {
            return Storage::disk('s3')->exists($path);
        } catch (Throwable $e) {
            throw new StorageException('Não foi possível verificar a existência do arquivo. Tente novamente.', 0, $e);
        }
    }
}
