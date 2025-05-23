<?php

namespace App\Accounts\Application\Query;

use App\Shared\Domain\Bus\QueryMessageInterface;

readonly class GetAccountInCsvQuery implements QueryMessageInterface
{
    public function __construct(
        public \DateTimeImmutable $updateDate
    ) {
    }
}
