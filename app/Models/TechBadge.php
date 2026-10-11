<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TechBadge extends Model
{
    protected $fillable = ['icon', 'title', 'subtitle', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function iconUrl(): ?string
    {
        return $this->icon ? Storage::url($this->icon) : null;
    }

    /**
     * Font Awesome icon + brand colour used when no icon was uploaded,
     * picked from keywords in the title. (The theme's old fallback cycled
     * through unrelated stock art — an Instagram logo on "Razorpay & Stripe",
     * Mailchimp on "ERP & HRMS".)
     *
     * @return array{0: string, 1: string}
     */
    public function fallbackIcon(): array
    {
        $title = strtolower($this->title.' '.$this->subtitle);

        $map = [
            'react' => ['fa-brands fa-react', '#149eca'],
            'node' => ['fa-brands fa-node-js', '#539e43'],
            'aws' => ['fa-brands fa-aws', '#ff9900'],
            'azure' => ['fa-brands fa-microsoft', '#0078d4'],
            'cloud' => ['fa-solid fa-cloud', '#0078d4'],
            'razorpay' => ['fa-solid fa-credit-card', '#635bff'],
            'stripe' => ['fa-brands fa-stripe-s', '#635bff'],
            'payment' => ['fa-solid fa-credit-card', '#635bff'],
            'erp' => ['fa-solid fa-building-user', '#2924f3'],
            'hrms' => ['fa-solid fa-users-gear', '#2924f3'],
            'graphql' => ['fa-solid fa-diagram-project', '#e10098'],
            'api' => ['fa-solid fa-plug', '#e10098'],
            'ai' => ['fa-solid fa-robot', '#10a37f'],
            'llm' => ['fa-solid fa-robot', '#10a37f'],
            'ci/cd' => ['fa-solid fa-infinity', '#01884c'],
            'devops' => ['fa-solid fa-infinity', '#01884c'],
            'python' => ['fa-brands fa-python', '#3776ab'],
            'laravel' => ['fa-brands fa-laravel', '#ff2d20'],
            'php' => ['fa-brands fa-php', '#777bb4'],
            'flutter' => ['fa-solid fa-mobile-screen', '#02569b'],
            'mobile' => ['fa-solid fa-mobile-screen', '#02569b'],
        ];

        foreach ($map as $keyword => $icon) {
            if (preg_match('/\b'.preg_quote($keyword, '/').'\b/', $title)) {
                return $icon;
            }
        }

        return ['fa-solid fa-code', '#2924f3'];
    }
}
