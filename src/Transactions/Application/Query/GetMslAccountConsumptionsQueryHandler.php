<?php

namespace App\Transactions\Application\Query;

use App\Shared\Domain\Bus\HandlerInterface;
use App\Transactions\Domain\Model\MslTransaction;
use App\Transactions\Infrastructure\Persistance\MslTransactionsRepositoryInterface;
use App\Transactions\Infrastructure\Symfony\Http\Response\AccountConsumptionSchema;

readonly class GetMslAccountConsumptionsQueryHandler implements HandlerInterface
{
    public function __construct(
        private MslTransactionsRepositoryInterface $mslTransactionsRepository
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(GetMslAccountConsumptionsQuery $query): AccountConsumptionSchema
    {
        [
            'totalPostTransactions' => $totalPostTransactions,
            'totalEcomTransactions' => $totalEcomTransactions,
            'totalApiTransactions' => $totalApiTransactions,
            'consumed' => $consumed,
            'rechargeSum' => $rechargeSum,
            'dischargeSum' => $dischargeSum,
        ] = $this->mslTransactionsRepository->getConsumptionsByAccountId(
            $query->accountId,
            $query->from,
            $query->to
        );

        $firstTransactionBeforeStartDate = $this->mslTransactionsRepository->findFistPreviousFromByAccountId(
            $query->accountId,
            $query->from
        );

        $initialBalance = $firstTransactionBeforeStartDate?->getLedgerBalance() ?? 0.0;

        if (0 == ($totalPostTransactions + $totalEcomTransactions + $totalApiTransactions)) {
            $lastBalance = $initialBalance;
        } else {
            $lastTransactionInPeriod = $this->mslTransactionsRepository->findLastTransactionByAccountId(
                $query->accountId,
                $query->from,
                $query->to
            );
            $lastBalance = $lastTransactionInPeriod?->getLedgerBalance() ?? 0.0;
        }

        $calculatedConsumption = $initialBalance - $lastBalance + ($rechargeSum - $dischargeSum);

        if (null !== $firstTransactionBeforeStartDate && MslTransaction::API_TRANSACTION_TYPE !== $firstTransactionBeforeStartDate->getTransactionType(
        )) {
            $cardId = $firstTransactionBeforeStartDate->getCardId() ?? null;
        } else {
            $cardId = null;
        }

        $coincidence = round($consumed - $calculatedConsumption, 3);

        $epsilon = 0.00001;

        if (abs((($initialBalance - $consumed) + ($rechargeSum - $dischargeSum)) - $lastBalance) < $epsilon) {
            $coincidence = 0.0;
        }

        return new AccountConsumptionSchema(
            $query->accountId,
            $cardId ?? '',
            $initialBalance,
            round($rechargeSum, 3),
            round($dischargeSum, 3),
            round($consumed, 3),
            $calculatedConsumption,
            $lastBalance,
            $totalPostTransactions,
            $totalEcomTransactions,
            $totalApiTransactions,
            -0 == $coincidence ? 0 : $coincidence,
            -0 == $coincidence ? 0 : $coincidence,
            -0 == $coincidence ? 0 : $coincidence,
        );
    }
}
