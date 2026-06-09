<?php

declare(strict_types=1);

namespace App\Integrations\Storage\Base;

use App\Integrations\Storage\Contract\StorageServiceInterface;

/**
 * Classe base abstrata para serviços de storage.
 * Fornece a estrutura comum e gerenciamento de estado
 * para implementações concretas (S3, local, etc.).
 */
abstract class StorageService implements StorageServiceInterface
{
    //
}
