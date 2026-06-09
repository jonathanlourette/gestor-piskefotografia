<?php

declare(strict_types=1);

namespace App\Domains\Gallery;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $gallery_photo_id
 * @property string $visitor_token
 * @property Carbon $created_at
 * @property-read GalleryPhoto $photo
 */
class GalleryFavorite extends Model
{
    protected $table = 'gallery_favorites';

    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'gallery_photo_id',
        'visitor_token',
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
     * Foto favoritada.
     */
    public function photo(): BelongsTo
    {
        return $this->belongsTo(GalleryPhoto::class, 'gallery_photo_id');
    }
}
