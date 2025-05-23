<?php

namespace App\Transactions\Application\Query;

use App\Shared\Domain\Bus\QueryMessageInterface;

class GetMslAccountsMoneyFlowQuery implements QueryMessageInterface
{
    public function __construct(
        public array $accountIdsGroups,
        public \DateTime $from,
        public \DateTime $to
    ) {
    }
}
