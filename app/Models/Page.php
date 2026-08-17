<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Page extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'content',
        'meta_title',
        'meta_description',
        'is_published',
        'is_locked',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'is_locked' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public const FOOTER_CACHE_KEY = 'pages.footer_links';

    /**
     * Published pages linked from the site footer. Cached forever and busted
     * whenever a page is written, so the footer costs no query per render.
     */
    public static function footerLinks(): \Illuminate\Support\Collection
    {
        return Cache::rememberForever(
            self::FOOTER_CACHE_KEY,
            fn () => static::published()->orderBy('title')->get(['id', 'slug', 'title'])
        );
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::FOOTER_CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::FOOTER_CACHE_KEY));
    }

    public function metaTitle(): string
    {
        return $this->meta_title ?: $this->title;
    }

    public function metaDescription(): string
    {
        return $this->meta_description
            ?: str(strip_tags((string) $this->content))->squish()->limit(155)->toString();
    }
}
