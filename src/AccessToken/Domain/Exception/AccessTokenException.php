<?php

namespace App\AccessToken\Domain\Exception;

use App\Shared\Domain\Contracts\CustomExceptionInterface;
use App\Shared\Domain\Exception\DomainException;

class AccessTokenException extends DomainException implements CustomExceptionInterface
{
    protected const DOMAIN = 'errors';

    public static function invalidId(): self
    {
        return new self('Invalid id', 406);
    }

    public static function invalidRole(): self
    {
        return new self('Invalid role', 406);
    }

    public static function invalidExistingEmail(): self
    {
        return new self('EmailNotificationMessage already in use', 406);
    }

    public static function invalidNotAccessTokenExist(): self
    {
        return new self('AccessToken not exist with this email', 406);
    }

    public static function invalidPermissions(): self
    {
        return new self('AccessToken not have enaugh permissions', 406);
    }

    public function getCustomCode(): string
    {
        return $this->error;
    }

    public function getDomain(): string
    {
        return self::DOMAIN;
    }

    private function __construct(public string $error, public int $status)
    {
        parent::__construct($error, $status);
    }
}
