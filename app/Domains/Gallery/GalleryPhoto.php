<?php

declare(strict_types=1);

namespace App\Domains\Gallery;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $gallery_id
 * @property string $s3_path
 * @property string $thumbnail_path
 * @property string $original_name
 * @property int $size_bytes
 * @property int $sort_order
 * @property Carbon $created_at
 * @property-read Gallery $gallery
 * @property-read Collection|GalleryFavorite[] $favorites
 */
class GalleryPhoto extends Model
{
    protected $table = 'gallery_photos';

    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'gallery_id',
        's3_path',
        'thumbnail_path',
        'original_name',
        'size_bytes',
        'sort_order',
        'created_at',
    ];

    /**
     * Indica se o modelo deve usar timestamps.
     */
    public $timestamps = false;

    /**
     * Define os casts de atributos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relacionamentos
    |--------------------------------------------------------------------------
    */

    /**
     * Galeria a qual esta foto pertence.
     */
    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class, 'gallery_id');
    }

    /**
     * Favoritos desta foto.
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(GalleryFavorite::class, 'gallery_photo_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Filtra fotos por galeria.
     */
    public function scopeByGallery(Builder $query, int $galleryId): Builder
    {
        return $query->where('gallery_id', $galleryId);
    }

    /**
     * Ordena fotos pela ordem de exibição.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order', 'asc');
    }
}
