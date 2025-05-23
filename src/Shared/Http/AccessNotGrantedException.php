<?php

namespace App\Shared\Http;

use App\Shared\Domain\Contracts\CustomExceptionInterface;
use App\Shared\Domain\Exception\DomainException;

class AccessNotGrantedException extends DomainException implements CustomExceptionInterface
{
    public const DOMAIN = 'errors';

    public function __construct(string $message = 'Access not granted', int $code = 400)
    {
        parent::__construct($message, $code);
    }

    public static function create(string $message, int $code = 400, array $parameters = []): self
    {
        $exception = new self($message, $code);
        $exception->setParameters($parameters);

        return $exception;
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
