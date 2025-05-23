<?php

namespace App\AccessToken\Domain\Model;

use App\AccessToken\Domain\Contracts\RoleInterface;
use App\AccessToken\Domain\Exception\AccessTokenException;

abstract class Role implements RoleInterface
{
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_CONSULTANT = 'ROLE_CONSULTANT';

    public const ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_CONSULTANT,
    ];

    private function __construct(public string $value)
    {
    }

    public static function cast(string $role): Role
    {
        $upperCaseRole = strtoupper($role);
        if (!in_array($upperCaseRole, self::ROLES)) {
            throw AccessTokenException::invalidRole();
        }

        return match ($role) {
            self::ROLE_ADMIN => new RoleAdmin(self::ROLE_ADMIN),
            default => new RoleConsultant(self::ROLE_CONSULTANT)
        };
    }

    public function equal(RoleInterface $other): bool
    {
        return $this->value === $other->getValue();
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public static function roleAdmin(): RoleInterface
    {
        return new RoleAdmin(self::ROLE_ADMIN);
    }

    public static function roleConsultant(): RoleInterface
    {
        return new RoleConsultant(self::ROLE_CONSULTANT);
    }
}
