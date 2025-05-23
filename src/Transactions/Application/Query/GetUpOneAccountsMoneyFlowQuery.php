<?php

namespace App\Transactions\Application\Query;

use App\Shared\Domain\Bus\QueryMessageInterface;

class GetUpOneAccountsMoneyFlowQuery implements QueryMessageInterface
{
    public function __construct(
        public string $organizationCif,
        public string $accountName,
        public \DateTime $from,
        public \DateTime $to
    ) {
    }
}
