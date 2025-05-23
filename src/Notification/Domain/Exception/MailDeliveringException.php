<?php

namespace App\Notification\Domain\Exception;

use App\Shared\Domain\Contracts\CustomExceptionInterface;
use App\Shared\Domain\Exception\DomainException;

class MailDeliveringException extends DomainException implements CustomExceptionInterface
{
    public const DOMAIN = 'errors';

    public static function create(string $message = 'services.mail.delivery.not_send', mixed $status = 400): self
    {
        $code = $status >= 400 ? $status : 400;

        return new self($message, $code);
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
