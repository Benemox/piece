<?php

namespace App\Transactions\Application\Query;

use App\Shared\Domain\Bus\HandlerInterface;
use App\Shared\Infrastructure\Redis\CacheServiceInterface;
use App\Transactions\Infrastructure\Persistance\MslTransactionsRepositoryInterface;
use App\Transactions\Infrastructure\Symfony\Http\Response\AccountConsumptionSchema;

class GetMslAccountsConsumptionsQueryHandler implements HandlerInterface
{
    public function __construct(
        private MslTransactionsRepositoryInterface $mslTransactionsRepository,
        private CacheServiceInterface $cache
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(GetMslAccountsConsumptionsQuery $query): array
    {
        $response = [];

        $consumptions = $this->getConsumptions($query);

        foreach ($consumptions as $accountConsumption) {
            $response[] = $this->getAccountConsumptionSchema($accountConsumption, $query->from, $query->to);
        }

        return $response;
    }

    private function calculatePaid(
        float $initialBalance,
        float $lastBalance,
        float $recharged,
        float $discharged
    ): float {
        return $initialBalance - $lastBalance + ($recharged - $discharged);
    }

    private function calculateCoincidence(float $calculatedPaid, float $consumed): float
    {
        $difference = round($consumed - $calculatedPaid, 3);

        return -0 == $difference ? 0 : $difference;
    }

    private function getConsumptions(GetMslAccountsConsumptionsQuery $query): array
    {
        $key = '-'.md5(
            'consumptions-'.$query->from->format('Y-m-d').$query->to->format(
                'Y-m-d'
            ).'-accounts:-'.md5(
                serialize($query->accountIds)
            )
        );

        $cached = $this->cache->keyExist($key) ? $this->cache->getFromStore(
            $key,
            'array'
        ) : null;

        if (null !== $cached) {
            return $cached;
        }

        $consumptions = $this->mslTransactionsRepository->getConsumptionsByAccountIds(
            $query->accountIds,
            $query->from,
            $query->to
        );

        $this->cache->store($key, $consumptions, $this->cache->getDefaultTtl() * 24, 'array');

        return $consumptions;
    }

    private function getAccountConsumptionSchema(
        array $accountConsumption,
        \DateTime $from,
        \DateTime $to
    ): AccountConsumptionSchema {
        $key = md5(
            'consumptions-'.$accountConsumption['account'].'-'.$from->format(
                'Y-m-d'
            ).'-'.$to->format('Y-m-d')
        );

        $existInCache = $this->cache->keyExist($key) ? $this->cache->getFromStore(
            $key,
            AccountConsumptionSchema::class
        ) : null;

        if (null !== $existInCache) {
            return $existInCache;
        }

        $account = $accountConsumption['account'];

        $firstTransaction = $this->mslTransactionsRepository->findFistPreviousFromByAccountId($account, $from);

        $cardId = $firstTransaction?->getCardId() ?? '';
        $initialBalance = $firstTransaction?->getLedgerBalance() ?? 0.0;
        $lastBalance = $this->mslTransactionsRepository->findLastBalanceByAccountId($account, $from, $to);

        $recharged = $accountConsumption['recharged'] ?? 0.0;
        $discharged = $accountConsumption['discharged'] ?? 0.0;
        $returned = $accountConsumption['returned'] ?? 0.0;
        $consumed = ($accountConsumption['consumed_pos_ecom'] ?? 0.0) - $returned;

        $totalPos = $accountConsumption['total_pos'] ?? 0;
        $totalEcom = $accountConsumption['total_ecom'] ?? 0;
        $totalApi = $accountConsumption['total_api'] ?? 0;

        $calculatedPaid = $this->calculatePaid(
            $initialBalance,
            $lastBalance,
            $recharged,
            $discharged
        );
        $coincidence = $this->calculateCoincidence($calculatedPaid, $consumed);

        $accountConsumptions = new AccountConsumptionSchema(
            accountId: $account,
            cardId: $cardId,
            initialBalance: $initialBalance,
            recharge: $recharged,
            discharge: $discharged,
            paid: $consumed,
            calculatedPaid: $calculatedPaid,
            lastBalance: $lastBalance,
            posTransactionsCount: $totalPos,
            ecomTransactionsCount: $totalEcom,
            apiTransactionCount: $totalApi,
            coincidenceInLastBalance: $coincidence,
            coincidenceInPayments: $coincidence,
            coincidence: $coincidence
        );

        $this->cache->store(
            $key,
            $accountConsumptions,
            $this->cache->getDefaultTtl() * 24,
            AccountConsumptionSchema::class
        );

        return $accountConsumptions;
    }
}
