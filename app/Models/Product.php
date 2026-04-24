<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'length_label',
        'color_label',
        'description',
        'short_description',
        'price',
        'original_price',
        'stock',
        'image_url',
        'badge',
        'is_active',
        'is_featured',
        'is_new',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_new' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'product_media')
            ->withPivot('sort_order')
            ->orderBy('product_media.sort_order');
    }

    public function getVideosAttribute()
    {
        return $this->media->filter(fn (Media $m) => $m->type === 'video');
    }

    public function getGalleryImagesAttribute()
    {
        return $this->media->filter(fn (Media $m) => $m->type === 'image');
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 0, ',', ' ').' FCFA';
    }

    public function getFormattedOriginalPriceAttribute(): ?string
    {
        if (! $this->original_price) {
            return null;
        }

        return number_format($this->original_price, 0, ',', ' ').' FCFA';
    }

    public function getDiscountPercentAttribute(): ?int
    {
        if (! $this->original_price || $this->original_price <= $this->price) {
            return null;
        }

        return (int) round((1 - $this->price / $this->original_price) * 100);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
