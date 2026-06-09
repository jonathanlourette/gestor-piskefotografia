<?php

declare(strict_types=1);

namespace App\Domains\Order;

use App\Domains\Order\Enums\OrderStatusEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uuid
 * @property string $customer_name
 * @property string $customer_phone
 * @property OrderStatusEnum $status
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|OrderItem[] $items
 * @property-read Collection|OrderPhoto[] $photos
 */
class Order extends Model
{
    protected $table = 'orders';

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
            'status' => OrderStatusEnum::class,
        ];
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->uuid)) {
                $order->uuid = Str::uuid()->toString();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relacionamentos
    |--------------------------------------------------------------------------
    */

    /**
     * Itens do pedido.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    /**
     * Todas as fotos do pedido (através dos itens).
     */
    public function photos(): HasManyThrough
    {
        return $this->hasManyThrough(
            OrderPhoto::class,
            OrderItem::class,
            'order_id',
            'order_item_id',
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Filtra pedidos por status.
     */
    public function scopeByStatus(Builder $query, OrderStatusEnum $status): Builder
    {
        return $query->where('status', $status->value);
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
     * Calcula o total do pedido somando os subtotais dos itens.
     */
    public function total(): float
    {
        $this->loadMissing('items');

        return (float) $this->items->sum(fn (OrderItem $item) => $item->subtotal());
    }

    /**
     * Retorna o total de fotos enviadas no pedido.
     */
    public function photosCount(): int
    {
        $this->loadMissing('items.photos');

        return (int) $this->items->sum(fn (OrderItem $item) => $item->photosCount());
    }
}
