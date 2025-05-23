<?php

namespace App\Transactions\Infrastructure\Symfony\Http\Response;

readonly class AccountConsumptionSchema implements \JsonSerializable
{
    public function __construct(
        private string $accountId,
        private string $cardId,
        private float $initialBalance,
        private float $recharge,
        private float $discharge,
        private float $paid,
        private float $calculatedPaid,
        private float $lastBalance,
        private int $posTransactionsCount,
        private int $ecomTransactionsCount,
        private int $apiTransactionCount,
        private float $coincidenceInLastBalance,
        private float $coincidenceInPayments,
        private float $coincidence
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'accountId' => $this->accountId,
            'cardId' => $this->cardId,
            'initialBalance' => round($this->initialBalance, 3),
            'recharge' => round($this->recharge, 3),
            'discharge' => round($this->discharge, 3),
            'paid' => round($this->paid, 3),
            'calculatedPaid' => round($this->calculatedPaid, 3),
            'lastBalance' => round($this->lastBalance, 3),
            'posTransactionsCount' => $this->posTransactionsCount,
            'ecomTransactionsCount' => $this->ecomTransactionsCount,
            'apiTransactionCount' => $this->apiTransactionCount,
            'coincidenceInLastBalance' => $this->coincidenceInLastBalance,
            'coincidenceInPayments' => $this->coincidenceInPayments,
            'coincidence' => $this->coincidence,
        ];
    }
}
