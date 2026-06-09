<?php

declare(strict_types=1);

namespace App\Domains\Order;

use App\Integrations\Storage\Contract\StorageServiceInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $order_item_id
 * @property string $s3_path
 * @property string $original_name
 * @property int|null $size_bytes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read OrderItem $orderItem
 */
class OrderPhoto extends Model
{
    protected $table = 'order_photos';

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
            'size_bytes' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relacionamentos
    |--------------------------------------------------------------------------
    */

    /**
     * Item do pedido ao qual esta foto pertence.
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Retorna a URL temporária assinada da foto no S3.
     */
    public function getTemporaryUrlAttribute(): string
    {
        return app(StorageServiceInterface::class)->getUrl($this->s3_path);
    }
}
