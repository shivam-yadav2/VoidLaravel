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
}
