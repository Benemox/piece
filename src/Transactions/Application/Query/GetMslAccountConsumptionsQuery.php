<?php

namespace App\Transactions\Application\Query;

use App\Shared\Domain\Bus\QueryMessageInterface;

class GetMslAccountConsumptionsQuery implements QueryMessageInterface
{
    public function __construct(
        public string $accountId,
        public \DateTime $from,
        public \DateTime $to
    ) {
    }
}
