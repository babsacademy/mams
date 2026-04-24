<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentProvider extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'display_name',
        'description',
        'is_enabled',
        'environment',
        'api_key',
        'api_secret',
        'webhook_secret',
        'merchant_id',
        'extra_config',
        'integration_guide',
        'logo_url',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'extra_config' => 'array',
            'api_key' => 'encrypted',
            'api_secret' => 'encrypted',
            'webhook_secret' => 'encrypted',
        ];
    }

    public static function getBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    public static function getEnabled(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_enabled', true)->orderBy('sort_order')->get();
    }

    public function isProduction(): bool
    {
        return $this->environment === 'production';
    }

    public function isSandbox(): bool
    {
        return $this->environment === 'sandbox';
    }
}
