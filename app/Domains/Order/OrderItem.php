<?php

declare(strict_types=1);

namespace App\Domains\Order;

use App\Domains\Product\Product;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property int $quantity
 * @property float $unit_price
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Order $order
 * @property-read Product $product
 * @property-read Collection|OrderPhoto[] $photos
 */
class OrderItem extends Model
{
    protected $table = 'order_items';

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
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relacionamentos
    |--------------------------------------------------------------------------
    */

    /**
     * Pedido ao qual este item pertence.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Produto associado a este item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Fotos enviadas para este item.
     */
    public function photos(): HasMany
    {
        return $this->hasMany(OrderPhoto::class, 'order_item_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Métodos de Domínio
    |--------------------------------------------------------------------------
    */

    /**
     * Calcula o subtotal do item (quantidade × preço unitário).
     */
    public function subtotal(): float
    {
        return (float) ($this->quantity * $this->unit_price);
    }

    /**
     * Retorna a quantidade de fotos enviadas para este item.
     */
    public function photosCount(): int
    {
        $this->loadMissing('photos');

        return $this->photos->count();
    }

    /**
     * Retorna o limite total de fotos do item (limite do produto × quantidade).
     */
    public function photoLimit(): int
    {
        $this->loadMissing('product');

        return (int) $this->product->photo_limit * $this->quantity;
    }
}
