<?php

namespace App\Tests\Behat\Persistence;

class BehatVariablesDatabase
{
    /**
     * @var array
     */
    protected static $storage = [];

    public static function reset(): void
    {
        self::$storage = [];
    }

    public static function set(string $key, $data): void
    {
        self::$storage[$key] = $data;
    }

    public static function get(string $key)
    {
        return self::$storage[$key] ?? null;
    }
}
