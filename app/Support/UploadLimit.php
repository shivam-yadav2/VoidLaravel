<?php

namespace App\Support;

/**
 * What PHP will actually accept per upload / per request on this server, so
 * admin forms can state the real limit instead of only the app's own rule
 * (a 50 MB rule is meaningless if php.ini caps uploads at 2 MB).
 */
class UploadLimit
{
    /** Effective per-file limit in KB, capped by $ruleKb. */
    public static function fileKb(int $ruleKb): int
    {
        return (int) min($ruleKb, floor(self::iniBytes('upload_max_filesize') / 1024), floor(self::iniBytes('post_max_size') / 1024));
    }

    public static function human(int $kb): string
    {
        return $kb >= 1024 ? rtrim(rtrim(number_format($kb / 1024, 1), '0'), '.').' MB' : $kb.' KB';
    }

    public static function postHuman(): string
    {
        return self::human((int) floor(self::iniBytes('post_max_size') / 1024));
    }

    private static function iniBytes(string $key): int
    {
        $value = trim((string) ini_get($key));
        if ($value === '' || $value === '0' || $value === '-1') {
            return PHP_INT_MAX;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        return (int) match ($unit) {
            'g' => $number * 1024 ** 3,
            'm' => $number * 1024 ** 2,
            'k' => $number * 1024,
            default => $number,
        };
    }
}
