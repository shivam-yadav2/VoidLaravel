<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Redirect extends Model
{
    protected $fillable = ['from_path', 'to_path', 'status_code', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /**
     * Normalizes to a leading-slash, no-trailing-slash path (or a full URL
     * left untouched) so "/old-page", "old-page" and "/old-page/" all match
     * the same stored redirect.
     */
    public static function normalize(string $path): string
    {
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $path = '/'.ltrim($path, '/');

        return $path === '/' ? $path : rtrim($path, '/');
    }

    public function setFromPathAttribute(string $value): void
    {
        $this->attributes['from_path'] = self::normalize($value);
    }
}
