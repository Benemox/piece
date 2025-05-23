<?php

namespace App\Commerces\Domain\Exception;

use App\Shared\Domain\Contracts\CustomExceptionInterface;
use App\Shared\Domain\Exception\DomainException;

class CommercesException extends DomainException implements CustomExceptionInterface
{
    protected const DOMAIN = 'errors';

    public function __construct(string $message, int $code = 400)
    {
        parent::__construct($message, $code);
    }

    public static function commerceNotFound(): self
    {
        return new self('Commerce not found', 404);
    }

    public function getCustomCode(): string
    {
        return $this->message;
    }

    public function getDomain(): string
    {
        return self::DOMAIN;
    }
}
