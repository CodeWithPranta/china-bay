<?php

namespace App\Models;

use App\Support\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'code', 'user_id', 'name', 'phone', 'address', 'district', 'thana',
        'delivery_method', 'delivery_charge', 'subtotal', 'discount', 'coupon_code',
        'total', 'payment_method', 'payment_status', 'trx_id', 'note', 'status',
    ];

    protected function casts(): array
    {
        return [
            'delivery_charge' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    /** @return HasMany<OrderItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function statusLabel(): string
    {
        return OrderStatus::label($this->status);
    }

    public function isCancellable(): bool
    {
        return OrderStatus::cancellable($this->status);
    }
}
