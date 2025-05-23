<?php

namespace App\Transactions\Application\Command;

use App\Shared\Domain\Bus\AsyncMessageInterface;

readonly class SaveMslTransactionCommand implements AsyncMessageInterface
{
    public function __construct(public array $transactionData)
    {
    }
}
