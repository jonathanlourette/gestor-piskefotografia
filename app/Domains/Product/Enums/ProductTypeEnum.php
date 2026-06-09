<?php

declare(strict_types=1);

namespace App\Domains\Product\Enums;

enum ProductTypeEnum: string
{
    case PACOTE_FOTOS = 'pacote_fotos';
    case QUADRO = 'quadro';
    case IMA = 'ima';
    case ALBUM = 'album';

    public function label(): string
    {
        return match ($this) {
            self::PACOTE_FOTOS => 'Pacote de Fotos',
            self::QUADRO => 'Quadro',
            self::IMA => 'Imã',
            self::ALBUM => 'Álbum',
        };
    }

    /**
     * Retorna a regra de validação para os valores do enum.
     */
    public static function validationRule(): string
    {
        return 'in:'.implode(',', array_column(self::cases(), 'value'));
    }

    /**
     * Retorna array de opções para selects HTML.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::PACOTE_FOTOS->value => self::PACOTE_FOTOS->label(),
            self::QUADRO->value => self::QUADRO->label(),
            self::IMA->value => self::IMA->label(),
            self::ALBUM->value => self::ALBUM->label(),
        ];
    }
}
