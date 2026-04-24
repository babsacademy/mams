<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'city',
        'delivery_zone',
        'delivery_address',
        'delivery_city',
        'delivery_notes',
        'subtotal',
        'delivery_fee',
        'total',
        'status',
        'notes',
        'coupon_code',
        'discount_amount',
        'paid',
        'payment_method',
        'placed_at',
    ];

    protected function casts(): array
    {
        return [
            'paid' => 'boolean',
            'placed_at' => 'datetime',
        ];
    }

    public const STATUSES = [
        'pending' => 'En attente',
        'confirmed' => 'Confirmée',
        'shipped' => 'Expédiée',
        'delivered' => 'Livrée',
        'cancelled' => 'Annulée',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total, 0, ',', ' ').' FCFA';
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = 'CMD-'.strtoupper(Str::random(10));
            }
        });
    }
}
