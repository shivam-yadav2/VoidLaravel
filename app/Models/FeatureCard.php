<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FeatureCard extends Model
{
    public const GROUP_SERVICES = 'services';
    public const GROUP_ABOUT_VALUES = 'about_values';

    public const GROUPS = [self::GROUP_SERVICES, self::GROUP_ABOUT_VALUES];

    protected $fillable = ['group', 'icon', 'title', 'description', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeGroup(Builder $query, string $group): Builder
    {
        return $query->where('group', $group);
    }

    public function iconUrl(): ?string
    {
        return $this->icon ? Storage::url($this->icon) : null;
    }
}
