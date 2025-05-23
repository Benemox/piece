<?php

namespace App\Settings\Domain\Exception;

use App\Shared\Domain\Contracts\CustomExceptionInterface;
use App\Shared\Domain\Exception\DomainException;

class SettingException extends DomainException implements CustomExceptionInterface
{
    protected const DOMAIN = 'errors';

    public static function invalidType(): self
    {
        return new self('Invalid setting type', 406);
    }

    public static function invalidName(): self
    {
        return new self('Invalid setting name', 406);
    }

    public static function notFound(): self
    {
        return new self('Invalid setting', 404);
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
