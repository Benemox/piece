<?php

namespace App\Transactions\Application\Command;

use App\Shared\Domain\Bus\AsyncMessageInterface;
use App\Transactions\Domain\Model\UpOneTransaction;

readonly class SaveUpOneTransactionCommand implements AsyncMessageInterface
{
    public function __construct(
        public UpOneTransaction $transaction
    ) {
    }
}
