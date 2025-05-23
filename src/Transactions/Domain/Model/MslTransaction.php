<?php

namespace App\Transactions\Domain\Model;

use App\Shared\Domain\AggregateRoot;
use App\Transactions\Domain\Contracts\RegistryInterface;

class MslTransaction extends AggregateRoot implements RegistryInterface
{
    public const string DEFAULT_TIMEZONE = 'Europe/Madrid';

    /**
     * ETL to save the transaction.
     *
     * @var array
     */
    public const array TRANSFORM_FIELDS = [
        'cardholder_billing_amount' => 'getPaid',
    ];

    public const string API_TRANSACTION_TYPE = 'API';
    public const string POS_TRANSACTION_TYPE = 'POS';
    public const string ECOM_TRANSACTION_TYPE = 'ECOM';

    public const string CREDIT_IMPACT = 'CR';
    public const string DEBIT_IMPACT = 'DR';

    public const string DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @throws \Exception
     */
    public function __construct(
        private array $data
    ) {
    }

    /**
     * ETL to save the transaction.
     */
    public function getTransformedRawData(): array
    {
        foreach ($this->data as $key => $value) {
            if (array_key_exists($key, self::TRANSFORM_FIELDS)) {
                $method = self::TRANSFORM_FIELDS[$key];
                $this->data[$key.'_transformed'] = $this->$method();
            }
        }

        return $this->data;
    }

    public function getTransactionId(): ?string
    {
        return $this->data['transaction_id'] ?? null;
    }

    public function getCardId(): ?string
    {
        return $this->data['card_id'] ?? null;
    }

    public function getAccountId(): ?string
    {
        return $this->data['account'] ?? null;
    }

    public function getMcc(): ?string
    {
        return $this->data['mcc'] ?? null;
    }

    public function getBalance(): ?float
    {
        return $this->data['available_balance'] ?? null;
    }

    public function getAcceptorPostal(): ?string
    {
        return $this->data['card_acceptor_postal'] ?? null;
    }

    public function getAcceptorCountry(): ?string
    {
        return $this->data['card_acceptor_country'] ?? null;
    }

    public function getAcceptorName(): ?string
    {
        return $this->data['card_acceptor_name'] ?? null;
    }

    public function getAcceptorCity(): ?string
    {
        return $this->data['card_acceptor_city'] ?? null;
    }

    public function getAcceptorTerminalId(): ?string
    {
        return $this->data['card_acceptor_terminalid'] ?? null;
    }

    public function getAcceptorId(): ?string
    {
        return $this->data['card_acceptor_id'] ?? null;
    }

    public function getTransactionType(): ?string
    {
        return $this->data['card_trantype'] ?? null;
    }

    public function getHoldFlag(): ?string
    {
        return $this->data['holdflag'] ?? null;
    }

    public function getTransactionDescription(): ?string
    {
        return $this->data['transaction_desc'] ?? null;
    }

    public function getImpact(): ?string
    {
        return $this->data['financial_impact_type'] ?? null;
    }

    public function getLedgerBalance(): ?float
    {
        return $this->data['ledger_balance'] ?? null;
    }

    /**
     * @throws \Exception
     */
    public function getPaid(): ?float
    {
        if (self::API_TRANSACTION_TYPE === $this->getTransactionType()) {
            return $this->getPaidAmountForApiTransactions();
        } else {
            return $this->getPaidAmountForPosEcomTransactions();
        }
    }

    /**
     * @throws \Exception
     */
    public function getPaidAmountForPosEcomTransactions(): ?float
    {
        $value = $this->data['cardholder_billing_amount'] ?? null;

        if (12 == strlen($value) and preg_match('/0*[1-9]\d*/', $value)) {
            return (float) ($value / 100);
        }

        return $value;
    }

    /**
     * @throws \Exception
     */
    public function getPaidAmountForApiTransactions(): ?float
    {
        $value = $this->data['cardholder_billing_amount'] ?? 0.0;

        return $value / 100;
    }

    /**
     * choose the correct paid amount.
     *
     * @throws \Exception
     */
    public function inferPaidAmount(): ?float
    {
        $startDate = new \DateTime('15-10-2024 00:00:00', new \DateTimeZone(self::DEFAULT_TIMEZONE));

        if ($this->getTime() > $startDate) {
            if ($this->getParsedPaidAmount()) {
                return $this->getParsedPaidAmount();
            } else {
                return $this->getPaidRaw();
            }
        }

        return $this->getPaid();
    }

    public function getPaidRaw(): ?float
    {
        return $this->data['cardholder_billing_amount'] ?? null;
    }

    public function getParsedPaidAmount(): ?float
    {
        return $this->data['cardholder_billing_amount_transformed'] ?? null;
    }

    public function getAuthId(): ?string
    {
        return $this->data['auth_id'] ?? null;
    }

    /**
     * @throws \Exception
     */
    public function getOriginalTransactionTime(): \DateTime
    {
        try {
            $originalTs = (array_key_exists(
                'original_ts',
                $this->data
            )) ? new \DateTime(
                $this->data['original_ts'],
                new \DateTimeZone('UTC')
            ) : null;
        } catch (\Exception) {
            return $this->getTime();
        }

        if (null === $originalTs) {
            return $this->getTime();
        }

        $originalTs->setTimezone(new \DateTimeZone(self::DEFAULT_TIMEZONE));

        return $originalTs;
    }

    /**
     * @throws \Exception
     */
    public function getTime(): \DateTime
    {
        $transactionDate = array_key_exists('logtimestamp', $this->data) ? new \DateTime(
            $this->data['logtimestamp'],
            new \DateTimeZone('UTC')
        ) : null;

        if (null === $transactionDate) {
            return new \DateTime('now', new \DateTimeZone(self::DEFAULT_TIMEZONE));
        }

        $transactionDate->setTimezone(new \DateTimeZone(self::DEFAULT_TIMEZONE));

        return $transactionDate;
    }

    public function setType(string $type): void
    {
        $this->data['type'] = $type;
    }

    public function getRawData(): array
    {
        return $this->getTransformedRawData();
    }

    public function enrichWithAccountDetails(
        string $cif,
        string $clientCode,
        string $memberName,
        string $memberSurname,
        string $mslCustomerId,
        ?string $organizationName,
        ?string $organizationId,
        ?string $productCode,
        ?string $productName,
        ?string $productId,
    ): void {
        $this->data['extra.cif'] = $cif;
        $this->data['extra.client_code'] = $clientCode;
        $this->data['extra.member_name'] = $memberName;
        $this->data['extra.member_surname'] = $memberSurname;
        $this->data['extra.product_name'] = $productName;
        $this->data['extra.product_id'] = $productId;
        $this->data['extra.msl_customer_id'] = $mslCustomerId;
        $this->data['extra.organization_name'] = $organizationName;
        $this->data['extra.organization_id'] = $organizationId;
        $this->data['extra.product_code'] = $productCode;
    }

    public function enrichWithCommerceDetails(
        string $code,
        string $name,
        string $csb,
        string $cifNif,
        string $area,
        string $province,
        string $address,
        ?string $sectorInt,
        ?string $sectorAct
    ): void {
        $this->data['extra.fuc.commerce_code'] = $code;
        $this->data['extra.fuc.commerce_name'] = $name;
        $this->data['extra.fuc.csb'] = $csb;
        $this->data['extra.fuc.cif_nif'] = $cifNif;
        $this->data['extra.fuc.area'] = $area;
        $this->data['extra.fuc.province'] = $province;
        $this->data['extra.fuc.address'] = $address;
        $this->data['extra.fuc.sector_int'] = $sectorInt;
        $this->data['extra.fuc.sector_act'] = $sectorAct;
    }

    public function getExtraCif(): ?string
    {
        if (!array_key_exists('extra.cif', $this->data)) {
            return null;
        }

        return $this->data['extra.cif'];
    }

    public function getExtraClientCode(): ?string
    {
        if (!array_key_exists('extra.client_code', $this->data)) {
            return null;
        }

        return $this->data['extra.client_code'];
    }

    public function getExtraMemberName(): ?string
    {
        if (!array_key_exists('extra.member_name', $this->data)) {
            return null;
        }

        return $this->data['extra.member_name'];
    }

    public function getExtraMemberSurname(): ?string
    {
        if (!array_key_exists('extra.member_surname', $this->data)) {
            return null;
        }

        return $this->data['extra.member_surname'];
    }

    public function getExtraProductName(): ?string
    {
        if (!array_key_exists('extra.product_name', $this->data)) {
            return null;
        }

        return $this->data['extra.product_name'];
    }

    public function getExtraProductId(): ?string
    {
        if (!array_key_exists('extra.product_id', $this->data)) {
            return null;
        }

        return $this->data['extra.product_id'];
    }
}
