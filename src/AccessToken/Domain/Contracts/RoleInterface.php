<?php

namespace App\AccessToken\Domain\Contracts;

interface RoleInterface
{
    public static function cast(string $role): RoleInterface;

    public function equal(RoleInterface $other): bool;

    public function getValue(): string;
}
