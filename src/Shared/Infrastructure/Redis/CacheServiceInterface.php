<?php

namespace App\Shared\Infrastructure\Redis;

interface CacheServiceInterface
{
    public function invalidate(string $key): void;

    public function getRawData(string $key): ?string;

    public function keyExist(string $key): bool;

    public function store(string $key, mixed $value, int $ttl, string $model): void;

    public function getFromStore(string $key, string $model): mixed;

    public function getDefaultTtl(): int;

    public function getMonthTtl(): int;
}
