<?php

namespace App\Transactions\Infrastructure\Persistance;

use App\Shared\Domain\Contracts\UidGeneratorInterface;
use App\Shared\Infrastructure\Elastica\ElasticaClientInterface;
use App\Transactions\Domain\Model\UpOneTransaction;

class ElasticUpOneTransactionRepositoryMsl implements UpOneTransactionsRepositoryInterface
{
    public function __construct(
        private ElasticaClientInterface $elasticaClient,
        private UidGeneratorInterface $uuidGenerator,
        private readonly string $upOneTransactionsIndex,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function save(UpOneTransaction $transaction): void
    {
        $this->elasticaClient->addRegistry(
            $this->upOneTransactionsIndex,
            $transaction,
            $transaction->getTransactionId().'_'.$this->uuidGenerator->generate()->value(),
            $transaction->getTime() ?? null
        );
    }

    public function findByFinancialImpact(\DateTime $from, \DateTime $to, string $financialImpact): array
    {
        $result = $this->elasticaClient->criteriaSearch(
            $this->upOneTransactionsIndex,
            'financial_impact_type',
            $financialImpact,
            $from,
            $to
        );

        return array_map(static function ($item) {
            return new UpOneTransaction($item);
        }, $result);
    }

    public function findByTransactionId(
        string $transactionId,
        \DateTime $from,
        \DateTime $to,
        array $criteria = []
    ): ?UpOneTransaction {
        $result = $this->elasticaClient->criteriaSearch(
            $this->upOneTransactionsIndex,
            'transaction_id',
            $transactionId,
            $from,
            $to,
            $criteria
        );

        if (0 === count($result)) {
            return null;
        } else {
            return new UpOneTransaction($result[0]);
        }
    }

    /**
     * @return UpOneTransaction[]
     */
    public function findByAccountId(string $accountId, \DateTime $from, \DateTime $to, array $criteria = []): array
    {
        $result = $this->elasticaClient->criteriaSearch(
            $this->upOneTransactionsIndex,
            'account_id',
            $accountId,
            $from,
            $to,
            $criteria
        );

        return array_map(static function ($item) {
            return new UpOneTransaction($item);
        }, $result);
    }

    public function findByCifAndCriteria(string $cif, \DateTime $from, \DateTime $to, array $criteria = []): array
    {
        $result = $this->elasticaClient->criteriaSearch(
            $this->upOneTransactionsIndex,
            'cif',
            $cif,
            $from,
            $to,
            $criteria
        );

        return array_map(static function ($item) {
            return new UpOneTransaction($item);
        }, $result);
    }
}
