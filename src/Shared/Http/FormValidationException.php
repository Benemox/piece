<?php

namespace App\Shared\Http;

use App\Shared\Domain\Contracts\CustomExceptionInterface;

class FormValidationException extends \Exception implements CustomExceptionInterface
{
    protected const DOMAIN = 'errors';

    /**
     * @param array<string, mixed> $errors
     */
    public function __construct(private readonly array $errors)
    {
        parent::__construct('Invalid request', 400);
    }

    /**
     * @return array<string, mixed>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getCustomCode(): string
    {
        return $this->getMessage();
    }

    public function getDomain(): string
    {
        return self::DOMAIN;
    }
}
