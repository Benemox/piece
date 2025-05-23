<?php

namespace App\Accounts\Application\Query;

use App\Shared\Domain\Bus\QueryMessageInterface;

readonly class GetAccountsOrganizationsQuery implements QueryMessageInterface
{
    public function __construct()
    {
    }
}
