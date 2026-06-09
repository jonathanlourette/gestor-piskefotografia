<?php

declare(strict_types=1);

namespace App\Integrations\Storage\Contract;

interface StorageServiceInterface
{
    /**
     * Faz upload de um arquivo para o storage.
     *
     * @param  string  $path  Caminho de destino no storage.
     * @param  mixed  $fileContent  Conteúdo do arquivo (string ou resource).
     * @param  string  $contentType  Tipo MIME do arquivo.
     * @return string O caminho do arquivo armazenado.
     */
    public function upload(string $path, mixed $fileContent, string $contentType = 'image/jpeg'): string;

    /**
     * Retorna a URL de acesso ao arquivo.
     *
     * @param  string  $path  Caminho do arquivo no storage.
     * @return string URL de acesso.
     */
    public function getUrl(string $path): string;

    /**
     * Remove um arquivo do storage.
     *
     * @param  string  $path  Caminho do arquivo no storage.
     * @return bool True se removido com sucesso.
     */
    public function delete(string $path): bool;

    /**
     * Verifica se um arquivo existe no storage.
     *
     * @param  string  $path  Caminho do arquivo no storage.
     * @return bool True se o arquivo existe.
     */
    public function exists(string $path): bool;
}
