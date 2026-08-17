<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group'];

    public const CACHE_KEY = 'settings.all';

    /**
     * All settings as a flat key => value map, cached until a write occurs.
     *
     * @return array<string, string|null>
     */
    public static function all_settings(): array
    {
        try {
            // Deliberately outside the cache write: if the table doesn't exist
            // yet (first boot, before `php artisan migrate`), nothing gets
            // cached, so the very next request tries the database again
            // instead of being stuck on an empty result forever.
            return Cache::rememberForever(self::CACHE_KEY, fn () => static::query()->pluck('value', 'key')->all());
        } catch (QueryException $e) {
            Log::warning('Settings table unavailable, using config defaults.', ['error' => $e->getMessage()]);

            return [];
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $value = static::all_settings()[$key] ?? null;

        return ($value === null || $value === '') ? $default : $value;
    }

    /**
     * @param  array<string, string|null>  $values
     */
    public static function setMany(array $values, string $group = 'general'): void
    {
        foreach ($values as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
        }

        static::flush();
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::flush());
        static::deleted(fn () => static::flush());
    }
}
