<?php

namespace App\Shared\Domain\Model;

use App\Shared\Domain\Exception\ValidationException;

readonly class CustomDate
{
    public const DATE_TIME_FORMAT = 'd-m-Y H:i:s';
    public const DATE_FORMAT = 'd-m-Y';

    public const DATE_AMERICAN_FORMAT = 'Y-m-d';

    public const DATE_AMERICAN_FORMAT_SECONDARY = 'Ymd';

    private function __construct(
        public \DateTimeImmutable $value
    ) {
    }

    /**
     * @throws \Exception
     */
    public static function cast(string|int $date): self
    {
        try {
            $dateTime = new \DateTimeImmutable(strval($date));
        } catch (\Exception) {
            throw new ValidationException(['invalid_date' => 'The date is not correct']);
        }

        return new self($dateTime);
    }

    public function toStringDateTimeFormat(): string
    {
        return $this->value->format(self::DATE_TIME_FORMAT);
    }

    public function toStringDateFormat(): string
    {
        return $this->value->format(self::DATE_FORMAT);
    }

    public function toStringAmericanDateFormat(): string
    {
        return $this->value->format(self::DATE_AMERICAN_FORMAT);
    }

    public function toStringAmericanDateFormatSecondary(): string
    {
        return $this->value->format(self::DATE_AMERICAN_FORMAT_SECONDARY);
    }

    public function toFormat(string $format): string
    {
        return $this->value->format($format);
    }

    public function toDateTime(): \DateTime
    {
        return \DateTime::createFromImmutable($this->value);
    }

    public static function now(): self
    {
        return new self(new \DateTimeImmutable());
    }

    public static function fromDateTime(\DateTime $date): self
    {
        $dateTime = \DateTimeImmutable::createFromFormat(
            self::DATE_TIME_FORMAT,
            $date->format(self::DATE_TIME_FORMAT)
        );

        if (false === $dateTime) {
            throw new ValidationException(['invalid_date' => 'The date is not correct']);
        }

        return new self($dateTime);
    }
}
