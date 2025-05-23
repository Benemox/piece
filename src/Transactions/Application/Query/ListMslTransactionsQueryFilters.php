<?php

namespace App\Transactions\Application\Query;

use _PHPStan_11268e5ee\Nette\InvalidArgumentException;

class ListMslTransactionsQueryFilters
{
    public const TRANSACTION_TYPES = [
        'API', // API transactions
        'POS', // Point of sale
        'ECOM', // Electronic commerce
    ];

    public const HOLD_FLAGS = [
        'Y', // Pre-accepted
        'N', // Cancelled
        'C', // Accepted
        'E',  // Expired
    ];

    public const FINANCIAL_IMPACT_TYPES = [
        'DR', // Debit
        'CR',  // Credit
    ];

    private array $filters = [];

    public function withFrom(string $from): self
    {
        $this->setFilterValue('from', $from);

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function getFrom(): \DateTime
    {
        return new \DateTime($this->getFilterValue('from'));
    }

    public function withTo(string $to): self
    {
        $this->setFilterValue('to', $to);

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function getTo(): \DateTime
    {
        return new \DateTime($this->getFilterValue('to'));
    }

    public function withTransactionType(string $transactionType): self
    {
        if (!in_array($transactionType, self::TRANSACTION_TYPES)) {
            throw new InvalidArgumentException('Invalid transaction type', 406);
        }

        $this->filters['card_trantype'] = $transactionType;

        return $this;
    }

    public function getTransactionType(): ?string
    {
        return $this->getFilterValue('card_trantype');
    }

    public function withProductId(string $productId): self
    {
        $this->setFilterValue('productId', $productId);

        return $this;
    }

    public function getProductId(): ?string
    {
        return $this->getFilterValue('productId');
    }

    public function withAccountName(string $accountName): self
    {
        $this->setFilterValue('account_name', $accountName);

        return $this;
    }

    public function getAccountName(): ?string
    {
        return $this->getFilterValue('account_name');
    }

    public function withHoldFlag(string $holderFlag): self
    {
        if (!in_array($holderFlag, self::HOLD_FLAGS)) {
            throw new InvalidArgumentException('Invalid hold flag', 406);
        }
        $this->setFilterValue('holdflag', $holderFlag);

        return $this;
    }

    public function getHoldFlag(): ?string
    {
        return $this->getFilterValue('holdflag');
    }

    public function withFinancialImpact(string $impact): self
    {
        if (!in_array($impact, self::FINANCIAL_IMPACT_TYPES)) {
            throw new InvalidArgumentException('Invalid financial impact type', 406);
        }
        $this->setFilterValue('financial_impact_type', $impact);

        return $this;
    }

    public function getFinancialImpact(): ?string
    {
        return $this->getFilterValue('financial_impact_type');
    }

    public function withCardIds(array $cardIds): self
    {
        $this->setFilterValue('card_ids', $cardIds);

        return $this;
    }

    public function getCardIds(): ?array
    {
        return $this->getFilterValue('card_ids');
    }

    public function withAccountId(array $accountId): self
    {
        $this->setFilterValue('accountIds', $accountId);

        return $this;
    }

    public function getAccountIds(): ?array
    {
        return $this->getFilterValue('accountIds');
    }

    private function getFilterValue(string $name): mixed
    {
        return $this->filters[$name] ?? null;
    }

    private function setFilterValue(string $name, mixed $value): void
    {
        $this->filters[$name] = is_string($value) ? trim($value) : $value;
    }
}
