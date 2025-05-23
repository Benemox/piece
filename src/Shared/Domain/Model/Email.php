<?php

namespace App\Shared\Domain\Model;

use App\Shared\Domain\Exception\ValidationException;

class Email implements \Stringable
{
    public function __construct(private string $value)
    {
    }

    public function value(): string
    {
        return strtolower($this->value);
    }

    public function equals(Email $email): bool
    {
        return $this->value === $email->value;
    }

    public static function cast(string $value): self
    {
        if (false === filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException(['Invalid email address']);
        }

        return new self($value);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
