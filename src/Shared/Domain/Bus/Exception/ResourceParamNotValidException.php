<?php

namespace App\Shared\Domain\Bus\Exception;

use App\Shared\Domain\Contracts\CustomExceptionInterface;
use App\Shared\Domain\Exception\DomainException;

class ResourceParamNotValidException extends DomainException implements CustomExceptionInterface
{
    protected const DOMAIN = 'errors';

    public static function create(string $message, array $parameters = [], int $code = 404): self
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
