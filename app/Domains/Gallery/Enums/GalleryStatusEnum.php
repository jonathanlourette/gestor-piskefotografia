<?php

declare(strict_types=1);

namespace App\Domains\Gallery\Enums;

enum GalleryStatusEnum: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case EXPIRED = 'expired';

    /**
     * Retorna as opções para select/dropdown.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $case) => [
            $case->value => match ($case) {
                self::DRAFT => 'Rascunho',
                self::ACTIVE => 'Ativa',
                self::EXPIRED => 'Expirada',
            },
        ])->toArray();
    }

    /**
     * Retorna a cor do badge para o status.
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::DRAFT => 'warning',
            self::ACTIVE => 'success',
            self::EXPIRED => 'secondary',
        };
    }

    /**
     * Retorna o label em português.
     */
    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Rascunho',
            self::ACTIVE => 'Ativa',
            self::EXPIRED => 'Expirada',
        };
    }
}
