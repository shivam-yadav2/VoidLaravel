<?php

namespace App\Support;

/**
 * Builds a `%term%` pattern for a LIKE search with the term's own LIKE
 * wildcards escaped, so searching for "100%" or "file_name" matches those
 * literal characters instead of "anything".
 */
class Like
{
    public static function contains(string $term): string
    {
        return '%'.addcslashes($term, '\\%_').'%';
    }
}
