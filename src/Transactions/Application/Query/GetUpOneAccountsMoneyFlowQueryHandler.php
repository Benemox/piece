<?php

namespace App\Transactions\Application\Query;

use App\Shared\Domain\Bus\HandlerInterface;
use App\Transactions\Domain\Model\UpOneTransaction;
use App\Transactions\Infrastructure\Persistance\UpOneTransactionsRepositoryInterface;

class GetUpOneAccountsMoneyFlowQueryHandler implements HandlerInterface
{
    public function __construct(
        private UpOneTransactionsRepositoryInterface $upOneTransactionsRepository
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(GetUpOneAccountsMoneyFlowQuery $query): array
    {
        $transactions = $this->upOneTransactionsRepository->findByCifAndCriteria(
            $query->organizationCif,
            $query->from,
            $query->to,
            [
                'account_name' => $query->accountName,
            ]
        );

        $recharge = 0;
        $discharge = 0;

        foreach ($transactions as $transaction) {
            if (UpOneTransaction::CREDIT === $transaction->getFinancialImpactType()) {
                $recharge += $transaction->getAmount();
            }
            if (UpOneTransaction::DEBIT === $transaction->getFinancialImpactType()) {
                $discharge += $transaction->getAmount();
            }
        }

        $difference = $recharge - $discharge;

        return [
            'recharge' => $recharge,
            'discharge' => $discharge,
            'difference' => round($difference, 3),
        ];
    }
}
