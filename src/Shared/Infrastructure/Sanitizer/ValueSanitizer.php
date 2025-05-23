<?php

namespace App\Shared\Infrastructure\Sanitizer;

enum ValueSanitizer: string
{
    case DEFAULT = 'DEFAULT';

    public static function default(): string
    {
        return '****';
    }

    public static function sanitize($format, $value): mixed
    {
        if (is_callable($format)) {
            return $format($value);
        }

        return match ($format) {
            self::DEFAULT => self::default(),
            default => $format,
        };
    }
}
