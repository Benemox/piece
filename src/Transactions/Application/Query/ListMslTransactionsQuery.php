<?php

namespace App\Transactions\Application\Query;

use App\Shared\Domain\Bus\QueryMessageInterface;
use App\Shared\Http\Pagination\Page;

class ListMslTransactionsQuery implements QueryMessageInterface
{
    public function __construct(
        public ListMslTransactionsQueryFilters $filters,
        public Page $page
    ) {
    }
}
