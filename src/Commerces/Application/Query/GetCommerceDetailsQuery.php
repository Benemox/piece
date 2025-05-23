<?php

namespace App\Commerces\Application\Query;

use App\Shared\Domain\Bus\QueryMessageInterface;

class GetCommerceDetailsQuery implements QueryMessageInterface
{
    public function __construct(
        public string $commerceId
    ) {
    }
}
