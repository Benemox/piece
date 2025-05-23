<?php

namespace App\Shared\Domain\Model;

use App\Shared\Domain\Exception\ValidationException;
use Symfony\Component\Uid\Uuid;

class Uid implements \Stringable
{
    final public const UID_REGEXP = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/';

    public static function cast(string $value): self
    {
        if (false === preg_match(self::UID_REGEXP, $value)) {
            throw new ValidationException(['Uid' => 'validate.fields.not_valid']);
        }

        return new self($value);
    }

    public static function castUuid(Uuid $uuid): self
    {
        return new self($uuid->toRfc4122());
    }

    public function __construct(public readonly string $value)
    {
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(Uid $id): bool
    {
        return $this->value === $id->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
