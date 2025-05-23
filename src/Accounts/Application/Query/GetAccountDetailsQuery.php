<?php

namespace App\Accounts\Application\Query;

use App\Shared\Domain\Bus\QueryMessageInterface;

readonly class GetAccountDetailsQuery implements QueryMessageInterface
{
    public function __construct(
        public string $accountId
    ) {
    }
}
