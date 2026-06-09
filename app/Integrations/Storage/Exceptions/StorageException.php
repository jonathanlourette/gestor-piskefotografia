<?php

declare(strict_types=1);

namespace App\Integrations\Storage\Exceptions;

use Exception;

/**
 * Exceção específica do domínio de Storage.
 * Encapsula erros de comunicação com serviços de armazenamento (S3, etc.).
 */
class StorageException extends Exception
{
    /**
     * @param  string  $message  Mensagem descritiva do erro de storage.
     * @param  Exception|null  $previous  Exceção original do vendor.
     */
    public function __construct(string $message = 'Não foi possível realizar a operação de storage. Tente novamente.', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
