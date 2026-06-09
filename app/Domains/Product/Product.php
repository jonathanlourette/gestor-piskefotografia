<?php

declare(strict_types=1);

namespace App\Domains\Product;

use App\Domains\Product\Enums\ProductTypeEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property float $price
 * @property int $photo_limit
 * @property ProductTypeEnum $type
 * @property string|null $image_path
 * @property bool $active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Product extends Model
{
    protected $table = 'products';

    /**
     * Os atributos que não são atribuíveis em massa.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

    /**
     * Define os casts de atributos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'photo_limit' => 'integer',
            'active' => 'boolean',
            'type' => ProductTypeEnum::class,
        ];
    }

    /**
     * Scope para filtrar produtos ativos.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * Scope para filtrar por tipo.
     */
    public function scopeByType(Builder $query, ProductTypeEnum $type): Builder
    {
        return $query->where('type', $type->value);
    }

    /**
     * Verifica se o produto é do tipo pacote de fotos.
     */
    public function isPacoteFotos(): bool
    {
        return $this->type === ProductTypeEnum::PACOTE_FOTOS;
    }

    /**
     * Verifica se o produto está ativo.
     */
    public function isActive(): bool
    {
        return $this->active;
    }
}
