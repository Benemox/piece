<?php

namespace App\Transactions\Domain\Exception;

use App\Shared\Domain\Contracts\CustomExceptionInterface;
use App\Shared\Domain\Exception\DomainException;

class TransactionsException extends DomainException implements CustomExceptionInterface
{
    protected const DOMAIN = 'errors';

    public function __construct(string $message, int $code = 400)
    {
        parent::__construct($message, $code);
    }

    public static function transactionNotFound(): self
    {
        return new self('Transaction not found', 404);
    }

    public static function missingTransactionId(): self
    {
        return new self('Missing transaction id', 406);
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
