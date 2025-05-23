<?php

namespace App\Accounts\Domain\Exception;

use App\Shared\Domain\Contracts\CustomExceptionInterface;
use App\Shared\Domain\Exception\DomainException;

class AccountException extends DomainException implements CustomExceptionInterface
{
    protected const DOMAIN = 'errors';

    public static function accountNotFound(): self
    {
        return new self('account not found', 404);
    }

    public function getCustomCode(): string
    {
        return $this->code;
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
