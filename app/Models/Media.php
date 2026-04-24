<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

/**
 * @property-read string $url
 * @property-read string $formatted_size
 * @property-read bool $is_image
 * @property-read bool $is_video
 */
class Media extends Model
{
    protected $fillable = [
        'filename',
        'original_name',
        'path',
        'disk',
        'size',
        'width',
        'height',
        'alt',
        'type',
        'mime_type',
        'duration',
        'thumbnail_path',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'duration' => 'integer',
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_media')
            ->withPivot('sort_order');
    }

    public function getUrlAttribute(): string
    {
        /** @var \Illuminate\Contracts\Filesystem\Cloud $disk */
        $disk = Storage::disk($this->disk);

        return $disk->url($this->path);
    }

    public function getFormattedSizeAttribute(): string
    {
        if ($this->size < 1024) {
            return $this->size.' o';
        }

        if ($this->size < 1048576) {
            return round($this->size / 1024, 1).' Ko';
        }

        return round($this->size / 1048576, 2).' Mo';
    }

    public function isImage(): bool
    {
        return $this->type === 'image';
    }

    public function isVideo(): bool
    {
        return $this->type === 'video';
    }

    public function getThumbnailUrlAttribute(): string
    {
        if ($this->thumbnail_path) {
            return Storage::disk($this->disk)->url($this->thumbnail_path);
        }

        // Fallback: pour les vidéos, retourner une URL de placeholder
        if ($this->isVideo()) {
            return 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Crect fill=%22%23333%22 width=%22100%22 height=%22100%22/%3E%3Cpath fill=%22white%22 d=%22M35 25v50l40-25z%22/%3E%3C/svg%3E';
        }

        return $this->url;
    }
}
