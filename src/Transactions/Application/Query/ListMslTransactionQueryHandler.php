<?php

namespace App\Transactions\Application\Query;

use App\Shared\Domain\Bus\HandlerInterface;
use App\Transactions\Domain\Model\MslTransaction;
use App\Transactions\Infrastructure\Persistance\MslTransactionsRepositoryInterface;
use App\Transactions\Infrastructure\Symfony\Http\Response\MslTransactionSchema;

class ListMslTransactionQueryHandler implements HandlerInterface
{
    public function __construct(
        private MslTransactionsRepositoryInterface $mslTransactionsRepository,
    ) {
    }

    public function __invoke(ListMslTransactionsQuery $query): array
    {
        $mslTransactions = $this->mslTransactionsRepository->findByFilters($query->filters, $query->page);

        $results = array_map(
            /**
             * @throws \Exception
             */ static function (MslTransaction $mslTransaction) {
                return new MslTransactionSchema($mslTransaction);
            },
            $mslTransactions
        );
        $totalResults = $this->mslTransactionsRepository->countByFilters($query->filters, $query->page);

        return [
            'totalElements' => $totalResults,
            'totalPages' => 0 === $totalResults ? 1 : (int) (ceil($totalResults / $query->page->limit())),
            'currentPage' => $query->page->pageNumber(),
            'data' => $results,
        ];
    }
}
