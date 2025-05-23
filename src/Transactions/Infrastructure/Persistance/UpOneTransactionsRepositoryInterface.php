<?php

namespace App\Transactions\Infrastructure\Persistance;

use App\Transactions\Domain\Model\UpOneTransaction;

interface UpOneTransactionsRepositoryInterface
{
    public function save(UpOneTransaction $transaction): void;

    public function findByTransactionId(
        string $transactionId,
        \DateTime $from,
        \DateTime $to,
        array $criteria = []
    ): ?UpOneTransaction;

    /**
     * @return UpOneTransaction[]
     */
    public function findByFinancialImpact(\DateTime $from, \DateTime $to, string $financialImpact): array;

    /**
     * @return UpOneTransaction[]
     */
    public function findByAccountId(string $accountId, \DateTime $from, \DateTime $to, array $criteria = []): array;

    /**
     * @return UpOneTransaction[]
     */
    public function findByCifAndCriteria(string $cif, \DateTime $from, \DateTime $to, array $criteria = []): array;
}
