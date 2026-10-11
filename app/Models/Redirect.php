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

    /**
     * Keep an old public URL working after its slug changes (e.g. a renamed
     * blog post): 301 the old path to the new one, collapse earlier renames
     * so chains go straight to the latest URL, and drop any redirect that
     * would now point the live URL away from itself.
     */
    public static function forRename(string $oldPath, string $newPath): void
    {
        $from = self::normalize($oldPath);
        $to = self::normalize($newPath);

        static::where('from_path', $to)->delete();
        static::where('to_path', $from)->update(['to_path' => $to]);

        static::updateOrCreate(
            ['from_path' => $from],
            ['to_path' => $to, 'status_code' => 301, 'is_active' => true],
        );
    }

    public function setFromPathAttribute(string $value): void
    {
        $this->attributes['from_path'] = self::normalize($value);
    }
}
