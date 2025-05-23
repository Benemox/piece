<?php

namespace App\Shared\Domain\Exception;

use App\Shared\Domain\Contracts\CustomExceptionInterface;

class ValidationException extends \DomainException implements \JsonSerializable, CustomExceptionInterface
{
    protected const DOMAIN = 'errors';

    protected array $errors;

    public function __construct(array $errors)
    {
        parent::__construct(sprintf('%s errors found.', \count($errors)), 400);

        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return array{errors: array}
     */
    public function jsonSerialize(): array
    {
        return [
            'errors' => $this->errors,
        ];
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
