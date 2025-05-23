<?php

namespace App\Transactions\Application\Command;

use App\Transactions\Domain\Model\MslTransaction;

interface TransactionEnricherInterface
{
    public function enrich(MslTransaction $transaction): MslTransaction;
}
