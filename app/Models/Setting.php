<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /** @var list<string> */
    protected $fillable = ['key', 'value', 'group'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, mixed $value, string $group = 'general'): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
    }

    public static function resolveMediaUrl(?string $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $normalizedValue = str_replace('\\', '/', trim($value));

        if (str_starts_with($normalizedValue, 'http://') || str_starts_with($normalizedValue, 'https://')) {
            return $normalizedValue;
        }

        if (str_starts_with($normalizedValue, '/storage/')) {
            return asset(ltrim($normalizedValue, '/'));
        }

        if (str_starts_with($normalizedValue, 'storage/')) {
            return asset($normalizedValue);
        }

        if (str_starts_with($normalizedValue, '/')) {
            return asset(ltrim($normalizedValue, '/'));
        }

        return asset('storage/'.ltrim($normalizedValue, '/'));
    }

    /** @return array<int, array{value: string, label: string, price: int}> */
    public static function shippingZones(): array
    {
        $json = static::get('shipping_zones', '[]');

        return json_decode($json, true) ?? [];
    }
}
