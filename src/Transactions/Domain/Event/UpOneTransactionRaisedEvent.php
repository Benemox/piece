<?php

namespace App\Transactions\Domain\Event;

use App\Shared\Domain\Bus\UpOneEventMessageInterface;
use App\Transactions\Domain\Model\UpOneTransaction;

readonly class UpOneTransactionRaisedEvent implements UpOneEventMessageInterface
{
    public function __construct(
        public array $data,
        public string $type
    ) {
    }

    public function castUpOneTransaction(): UpOneTransaction
    {
        $transaction = new UpOneTransaction(
            $this->data
        );
        $transaction->setType($this->type);

        return $transaction;
    }
}
