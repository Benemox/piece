<?php

namespace App\Transactions\Application\Query;

use App\Shared\Domain\Bus\QueryMessageInterface;

class GetMslAccountsConsumptionsQuery implements QueryMessageInterface
{
    public function __construct(
        public array $accountIds,
        public \DateTime $from,
        public \DateTime $to
    ) {
    }
}
