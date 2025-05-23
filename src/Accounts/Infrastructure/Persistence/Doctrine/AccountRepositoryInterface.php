<?php

namespace App\Accounts\Infrastructure\Persistence\Doctrine;

use App\Accounts\Application\Query\ListAccountsQueryFilters;
use App\Accounts\Domain\Model\Account;
use App\Shared\Http\Pagination\Page;

interface AccountRepositoryInterface
{
    /**
     * @return Account[]
     */
    public function findAll(): array;

    public function save(Account $account): void;

    public function findByAccountId(string $accountId): ?Account;

    /**
     * @return Account[]
     */
    public function findAfterDate(\DateTimeImmutable $updateDate): array;

    public function listAndFilter(ListAccountsQueryFilters $filters, Page $page): array;

    public function countByFilters(ListAccountsQueryFilters $filters, Page $page): int;

    public function getAccountsOrganizations(): array;
}
