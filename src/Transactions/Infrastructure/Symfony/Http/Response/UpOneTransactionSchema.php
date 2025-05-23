<?php

namespace App\Transactions\Infrastructure\Symfony\Http\Response;

use App\Transactions\Domain\Model\MslTransaction;
use App\Transactions\Domain\Model\UpOneTransaction;
use OpenApi\Attributes as OA;

#[OA\Schema(
    properties: [
        new OA\Property(
            property: 'dateTime',
            type: 'dateTime',
            format: 'date-time',
            example: '2024-09-11 12:38:07'
        ),
        new OA\Property(
            property: 'accountId',
            type: 'string'
        ),
        new OA\Property(
            property: 'accountName',
            type: 'string'
        ),
        new OA\Property(
            property: 'customerId',
            type: 'integer'
        ),
        new OA\Property(
            property: 'customerName',
            type: 'string'
        ),
        new OA\Property(
            property: 'customerSurname',
            type: 'string'
        ),
        new OA\Property(
            property: 'productId',
            type: 'integer'
        ),
        new OA\Property(
            property: 'productName',
            type: 'string'
        ),
        new OA\Property(
            property: 'cif',
            type: 'string'
        ),
        new OA\Property(
            property: 'amount',
            type: 'number'
        ),
        new OA\Property(
            property: 'financialImpactType',
            type: 'string'
        ),
        new OA\Property(
            property: 'transactionId',
            type: 'integer'
        ),
    ]
)]
readonly class UpOneTransactionSchema implements \JsonSerializable
{
    public function __construct(
        private UpOneTransaction $transaction,
        private ?MslTransaction $mslTransaction
    ) {
    }

    /**
     * @throws \Exception
     */
    public function jsonSerialize(): array
    {
        $data = [
            'dateTime' => $this->transaction->getTime()?->format(MslTransaction::DATE_TIME_FORMAT),
            'accountId' => $this->transaction->getAccountId(),
            'accountName' => $this->transaction->getAccountName(),
            'customerId' => $this->transaction->getCustomerId(),
            'customerName' => $this->transaction->getCustomerName(),
            'customerSurname' => $this->transaction->getCustomerSurname(),
            'cif' => $this->transaction->getCif(),
            'productName' => $this->transaction->getProductName(),
            'productId' => $this->transaction->getProductId(),
            'financialImpactType' => $this->transaction->getFinancialImpactType(),
            'amount' => $this->transaction->getAmount(),
            'transactionId' => $this->transaction->getTransactionId(),
        ];

        if (null !== $this->mslTransaction) {
            $data['cardId'] = $this->mslTransaction->getCardId();
            $data['ledgerBalance'] = $this->mslTransaction->getLedgerBalance();
            $data['actualBalance'] = $this->mslTransaction->getBalance();
            $data['paid'] = $this->mslTransaction->inferPaidAmount();
            $data['balanceImpact'] = $this->mslTransaction->getImpact();
            $data['transactionType'] = $this->mslTransaction->getTransactionType();
            $data['transactionDescription'] = $this->mslTransaction->getTransactionDescription();
            $data['establishment'] = $this->mslTransaction->getAcceptorName();
            $data['establishmentCity'] = $this->mslTransaction->getAcceptorCity();
            $data['establishmentCountry'] = $this->mslTransaction->getAcceptorCountry();
            $data['establishmentTerminaId'] = $this->mslTransaction->getAcceptorTerminalId();
            $data['establishmentPostalCode'] = $this->mslTransaction->getAcceptorPostal();
            $data['mcc'] = $this->mslTransaction->getMcc();
        }

        return $data;
    }
}
