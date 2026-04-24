<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    /** @use HasFactory<\Database\Factories\CouponFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order',
        'max_uses',
        'uses_count',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'expires_at' => 'datetime',
            'value'      => 'float',
            'min_order'  => 'float',
        ];
    }

    public function isValid(float $orderTotal): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_uses !== null && $this->uses_count >= $this->max_uses) {
            return false;
        }

        if ($this->min_order !== null && $orderTotal < $this->min_order) {
            return false;
        }

        return true;
    }

    public function discountFor(float $orderTotal): float
    {
        if ($this->type === 'percent') {
            return round($orderTotal * $this->value / 100, 2);
        }

        return min($this->value, $orderTotal);
    }
}
