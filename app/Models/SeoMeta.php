<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SeoMeta extends Model
{
    protected $table = 'seo_meta';

    protected $fillable = ['key', 'meta_title', 'meta_description', 'og_image', 'noindex'];

    public const CACHE_KEY = 'seo_meta.all';

    protected function casts(): array
    {
        return ['noindex' => 'boolean'];
    }

    /**
     * Every managed record keyed by page key, cached until a write occurs.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function map(): array
    {
        return Cache::rememberForever(
            self::CACHE_KEY,
            fn () => static::query()->get()->keyBy('key')->map->only([
                'meta_title', 'meta_description', 'og_image', 'noindex',
            ])->all()
        );
    }

    /**
     * Admin overrides for a page key, with blank fields stripped so they fall
     * through to the values the controller supplied.
     *
     * @return array<string, mixed>
     */
    public static function overridesFor(?string $key): array
    {
        if (! $key) {
            return [];
        }

        $row = static::map()[$key] ?? null;

        if (! $row) {
            return [];
        }

        return array_filter([
            'title' => $row['meta_title'] ?: null,
            'description' => $row['meta_description'] ?: null,
            'image' => $row['og_image'] ? Storage::url($row['og_image']) : null,
            'noindex' => $row['noindex'] ? true : null,
        ], fn ($v) => $v !== null);
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
