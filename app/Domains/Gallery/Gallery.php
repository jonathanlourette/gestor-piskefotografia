<?php

declare(strict_types=1);

namespace App\Domains\Gallery;

use App\Domains\Gallery\Enums\GalleryStatusEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uuid
 * @property string $title
 * @property string $customer_name
 * @property string $customer_email
 * @property string $customer_phone
 * @property string $password
 * @property string|null $cover_photo_path
 * @property GalleryStatusEnum $status
 * @property Carbon|null $expires_at
 * @property int $photos_count
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|GalleryPhoto[] $photos
 */
class Gallery extends Model
{
    protected $table = 'galleries';

    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'title',
        'customer_name',
        'customer_email',
        'customer_phone',
        'password',
        'cover_photo_path',
        'expires_at',
        'status',
        'photos_count',
    ];

    /**
     * Define os casts de atributos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => GalleryStatusEnum::class,
            'expires_at' => 'datetime',
        ];
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Gallery $gallery) {
            if (empty($gallery->uuid)) {
                $gallery->uuid = Str::uuid()->toString();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relacionamentos
    |--------------------------------------------------------------------------
    */

    /**
     * Fotos da galeria.
     */
    public function photos(): HasMany
    {
        return $this->hasMany(GalleryPhoto::class, 'gallery_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Filtra galerias por status.
     */
    public function scopeByStatus(Builder $query, GalleryStatusEnum $status): Builder
    {
        return $query->where('status', $status->value);
    }

    /**
     * Filtra galerias ativas.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', GalleryStatusEnum::ACTIVE->value);
    }

    /**
     * Ordena do mais recente para o mais antigo.
     */
    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc');
    }

    /*
    |--------------------------------------------------------------------------
    | Métodos de Domínio
    |--------------------------------------------------------------------------
    */

    /**
     * Retorna o caminho da foto de capa.
     */
    public function coverPhotoPath(): ?string
    {
        return $this->cover_photo_path;
    }
}
