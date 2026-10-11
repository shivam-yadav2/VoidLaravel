<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * A product Void Layer built and runs itself (SaaS, app, tool). Shown as a
 * card grid; clicking a card opens a detail modal (gallery, video, features,
 * link). Everything here is admin-managed at /admin/products.
 */
class Product extends Model
{
    public const STATUSES = [
        'live' => 'Live',
        'beta' => 'Beta',
        'coming_soon' => 'Coming soon',
    ];

    protected $fillable = [
        'name', 'slug', 'tagline', 'category', 'status', 'summary', 'description',
        'features', 'tech_stack', 'logo', 'cover_image', 'cover_image_alt',
        'video_path', 'video_url', 'product_url', 'cta_label',
        'is_published', 'is_featured', 'sort_order', 'meta_title', 'meta_description',
    ];

    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'is_featured' => 'boolean'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('is_featured')->orderBy('sort_order')->orderByDesc('id');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? self::STATUSES['live'];
    }

    public function logoUrl(): ?string
    {
        return $this->logo ? Storage::url($this->logo) : null;
    }

    public function coverUrl(): ?string
    {
        return $this->cover_image ? Storage::url($this->cover_image) : null;
    }

    public function ctaLabel(): string
    {
        return $this->cta_label ?: ($this->status === 'coming_soon' ? 'Join the waitlist' : 'Visit product');
    }

    /** @return list<string> */
    public function featureList(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $this->features))));
    }

    /** @return list<string> */
    public function techList(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $this->tech_stack))));
    }

    /**
     * The product's video as something the page can render: an uploaded file
     * wins; otherwise a YouTube / Vimeo link becomes a privacy-friendly embed.
     *
     * @return array{type: 'file'|'embed', src: string}|null
     */
    public function video(): ?array
    {
        if ($this->video_path) {
            return ['type' => 'file', 'src' => Storage::url($this->video_path)];
        }

        $embed = self::embedUrl($this->video_url);

        return $embed ? ['type' => 'embed', 'src' => $embed] : null;
    }

    /**
     * YouTube (watch, youtu.be, embed, shorts) or Vimeo link → embed URL, or
     * null for anything else. Also used to validate the admin field.
     */
    public static function embedUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        if (preg_match('~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $url, $m)) {
            return 'https://www.youtube-nocookie.com/embed/'.$m[1].'?rel=0&modestbranding=1&playsinline=1';
        }

        if (preg_match('~vimeo\.com/(?:video/)?(\d{6,12})~', $url, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1].'?dnt=1';
        }

        return null;
    }

    public function metaTitle(): string
    {
        return $this->meta_title ?: $this->name.($this->tagline ? ' — '.$this->tagline : '');
    }

    public function metaDescription(): string
    {
        return $this->meta_description ?: $this->summary;
    }
}
