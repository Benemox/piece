<?php

namespace App\Transactions\Infrastructure\Persistance;

use App\Shared\Http\Pagination\Page;
use App\Transactions\Application\Query\ListMslTransactionsQueryFilters;
use App\Transactions\Domain\Model\MslTransaction;

interface MslTransactionsRepositoryInterface
{
    public function save(MslTransaction $transaction): void;

    public function findByTransactionId(
        string $transactionId,
        \DateTime $from,
        \DateTime $to,
        array $criteria = []
    ): ?MslTransaction;

    public function getCardIdFromAccountId(string $accountId): ?string;

    /**
     * @return MslTransaction[]
     */
    public function findByAccountId(string $accountId, \DateTime $from, \DateTime $to, array $criteria = []): array;

    public function findFistPreviousFromByAccountId(
        string $accountId,
        \DateTime $from,
    ): ?MslTransaction;

    public function findInitialBalanceByAccountId(
        string $accountId,
        \DateTime $from,
    ): float;

    public function findLastTransactionByAccountId(
        string $accountId,
        \DateTime $from,
        \DateTime $to
    ): ?MslTransaction;

    public function findLastBalanceByAccountId(
        string $accountId,
        \DateTime $from,
        \DateTime $to
    ): float;

    public function getConsumedByAccountId(string $accountId, \DateTime $from, \DateTime $to): float;

    public function getRechargedByAccountId(string $accountId, \DateTime $from, \DateTime $to): float;

    public function getDischargedByAccountId(string $accountId, \DateTime $from, \DateTime $to): float;

    public function countPosTransactionsByAccountId(string $accountId, \DateTime $from, \DateTime $to): int;

    public function countEcomTransactionsByAccountId(string $accountId, \DateTime $from, \DateTime $to): int;

    public function countApiTransactionsByAccountId(string $accountId, \DateTime $from, \DateTime $to): int;

    public function getConsumptionsByAccountId(string $accountId, \DateTime $from, \DateTime $to): array;

    public function getConsumptionsByAccountIds(array $accountIds, \DateTime $from, \DateTime $to): array;

    public function getRechargedAndDischargedByAccountIds(array $accountIds, \DateTime $from, \DateTime $to): array;

    /**
     * @return MslTransaction[]
     */
    public function findByCardId(string $cardId, \DateTime $from, \DateTime $to, array $criteria = []): array;

    /**
     * @return MslTransaction[]
     */
    public function findByFilters(ListMslTransactionsQueryFilters $filters, Page $page): array;

    public function countByFilters(ListMslTransactionsQueryFilters $filters, Page $page): int;
}
