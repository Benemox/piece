<?php

namespace App\Accounts\Application\Query;

use App\Accounts\Domain\Model\Account;
use App\Accounts\Infrastructure\Persistence\Doctrine\AccountRepositoryInterface;
use App\Accounts\Infrastructure\Symfony\Model\Response\AccountSchema;
use App\Shared\Domain\Bus\HandlerInterface;

class ListAccountsQueryHandler implements HandlerInterface
{
    public function __construct(
        private AccountRepositoryInterface $accountRepository
    ) {
    }

    public function __invoke(ListAccountsQuery $query): array
    {
        $accounts = $this->accountRepository->listAndFilter($query->filters, $query->page);
        $total = $this->accountRepository->countByFilters($query->filters, $query->page);

        return [
            'total' => $total,
            'page' => $query->page->pageNumber(),
            'count' => $query->page->limit(),
            'results' => array_map(
                static function (Account $account) {
                    return new AccountSchema($account);
                },
                $accounts
            ),
        ];
    }
}
