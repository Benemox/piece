<?php

namespace App\Transactions\Domain\Model;

use App\Shared\Domain\AggregateRoot;
use App\Transactions\Domain\Contracts\RegistryInterface;

class UpOneTransaction extends AggregateRoot implements RegistryInterface
{
    public const CREDIT = 'credit';
    public const DEBIT = 'debit';

    public const CLIENT_CODE_KEY = 'as400';
    public const TRANSACTION_ID_KEY = 'transaction_id';
    public const ACCOUNT_ID_KEY = 'account_id';
    public const ACCOUNT_NAME_KEY = 'account_name';
    public const AMOUNT_KEY = 'amount';
    public const CIF_KEY = 'cif';
    public const ORGANIZATION_NAME_KEY = 'organization_name';
    public const ORGANIZATION_ID_KEY = 'organization_id';
    public const CUSTOMER_ID_KEY = 'customer_id';
    public const CUSTOMER_NAME_KEY = 'customer_name';
    public const CUSTOMER_SURNAME_KEY = 'customer_surname';
    public const PRODUCT_NAME_KEY = 'product_name';
    public const PRODUCT_ID_KEY = 'product_id';
    public const PRODUCT_CODE_KEY = 'product_code';
    public const FINANCIAL_IMPACT_TYPE_KEY = 'financial_impact_type';
    public const NOTIFICATION_TYPE_KEY = 'notification_type';
    public const DATETIME_KEY = 'datetime';

    public function __construct(
        private array $data
    ) {
    }

    public function getRawData(): array
    {
        return $this->data;
    }

    public function getTransactionId(): ?string
    {
        return $this->data[self::TRANSACTION_ID_KEY] ?? null;
    }

    public function getAccountId(): ?string
    {
        return $this->data[self::ACCOUNT_ID_KEY] ?? null;
    }

    public function getAccountName(): ?string
    {
        return $this->data[self::ACCOUNT_NAME_KEY] ?? null;
    }

    public function getAmount(): ?float
    {
        if (!array_key_exists(self::AMOUNT_KEY, $this->data)) {
            return null;
        }

        return round((float) $this->data[self::AMOUNT_KEY] / 100, 3);
    }

    public function getCif(): ?string
    {
        return $this->data[self::CIF_KEY] ?? null;
    }

    public function getCustomerId(): ?string
    {
        return $this->data[self::CUSTOMER_ID_KEY] ?? null;
    }

    public function getCustomerName(): ?string
    {
        return $this->data[self::CUSTOMER_NAME_KEY] ?? null;
    }

    public function getCustomerSurname(): ?string
    {
        return $this->data[self::CUSTOMER_SURNAME_KEY] ?? null;
    }

    public function getProductName(): ?string
    {
        return $this->data[self::PRODUCT_NAME_KEY] ?? null;
    }

    public function getProductCode(): ?string
    {
        return $this->data[self::PRODUCT_CODE_KEY] ?? null;
    }

    public function getProductId(): ?string
    {
        return $this->data[self::PRODUCT_ID_KEY]['value'] ?? null;
    }

    public function getFinancialImpactType(): ?string
    {
        return $this->data[self::FINANCIAL_IMPACT_TYPE_KEY] ?? null;
    }

    public function getNotificationType(): ?string
    {
        return $this->data[self::NOTIFICATION_TYPE_KEY] ?? null;
    }

    public function getClientCode(): ?string
    {
        if (!array_key_exists(self::CLIENT_CODE_KEY, $this->data)) {
            return null;
        }

        return str_replace('.', '', $this->data[self::CLIENT_CODE_KEY]);
    }

    public function getOrganizationName(): ?string
    {
        return $this->data[self::ORGANIZATION_NAME_KEY] ?? null;
    }

    public function getOrganizationId(): ?string
    {
        return $this->data[self::ORGANIZATION_ID_KEY] ?? null;
    }

    /**
     * @throws \Exception
     */
    public function getTime(): ?\DateTime
    {
        return $this->data[self::DATETIME_KEY] ? new \DateTime($this->data[self::DATETIME_KEY]) : null;
    }

    public function setType(string $type): void
    {
        $this->data['type'] = $type;
    }

    public function validate(): array
    {
        $errors = [];

        // Check required fields
        $requiredFields = [
            self::TRANSACTION_ID_KEY,
            self::ACCOUNT_ID_KEY,
            self::AMOUNT_KEY,
            self::DATETIME_KEY,
        ];

        foreach ($requiredFields as $field) {
            if (empty($this->data[$field])) {
                $errors[] = "Field '$field' is required.";
            }
        }

        // Validate amount
        if (isset($this->data[self::AMOUNT_KEY]) && !is_numeric($this->data[self::AMOUNT_KEY])) {
            $errors[] = 'Amount must be numeric.';
        }

        // Validate datetime
        if (isset($this->data[self::DATETIME_KEY])) {
            try {
                new \DateTime($this->data[self::DATETIME_KEY]);
            } catch (\Exception) {
                $errors[] = "Invalid date format for 'datetime'.";
            }
        }

        return $errors;
    }
}
