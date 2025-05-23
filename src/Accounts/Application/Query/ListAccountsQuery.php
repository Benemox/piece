<?php

namespace App\Accounts\Application\Query;

use App\Shared\Domain\Bus\QueryMessageInterface;
use App\Shared\Http\Pagination\Page;

readonly class ListAccountsQuery implements QueryMessageInterface
{
    public function __construct(
        public ListAccountsQueryFilters $filters,
        public Page $page
    ) {
    }
}
