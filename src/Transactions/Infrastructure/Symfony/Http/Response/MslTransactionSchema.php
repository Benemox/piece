<?php

namespace App\Transactions\Infrastructure\Symfony\Http\Response;

use App\Transactions\Domain\Model\MslTransaction;
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
            type: 'string',
            example: 'ES1234567890'
        ),
        new OA\Property(
            property: 'cardId',
            type: 'string',
            example: '1234567890123456'
        ),
        new OA\Property(
            property: 'ledgerBalance',
            type: 'integer',
            example: 1000
        ),
        new OA\Property(
            property: 'actualBalance',
            type: 'integer',
            example: 1000
        ),
        new OA\Property(
            property: 'paid',
            type: 'integer',
            example: 1000
        ),
        new OA\Property(
            property: 'balanceImpact',
            type: 'integer',
            example: 1000
        ),
        new OA\Property(
            property: 'transactionType',
            type: 'string',
            enum: [
                'POS',
                'ECOM',
                'API',
            ],
            example: 'POS'
        ),
        new OA\Property(
            property: 'transactionDescription',
            type: 'string',
            example: 'Transfer'
        ),
        new OA\Property(
            property: 'establishment',
            type: 'string',
            example: 'EL CORTING ENGLISH'
        ),
        new OA\Property(
            property: 'establishmentCity',
            type: 'string',
            example: 'ZARAGOZA'
        ),
        new OA\Property(
            property: 'establishmentCountry',
            type: 'string',
            example: 'ES'
        ),
        new OA\Property(
            property: 'establishmentTerminaId',
            type: 'string',
            example: '12345678'
        ),
        new OA\Property(
            property: 'establishmentPostalCode',
            type: 'string',
            example: '50001'
        ),
        new OA\Property(
            property: 'mcc',
            type: 'string',
            example: '5812'
        ),
    ]
)]
readonly class MslTransactionSchema implements \JsonSerializable
{
    public function __construct(
        private MslTransaction $transaction,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function jsonSerialize(): array
    {
        return [
            'dateTime' => $this->transaction->getTime()->format(MslTransaction::DATE_TIME_FORMAT),
            'accountId' => $this->transaction->getAccountId(),
            'cardId' => $this->transaction->getCardId(),
            'ledgerBalance' => $this->transaction->getLedgerBalance(),
            'actualBalance' => $this->transaction->getBalance(),
            'paid' => $this->transaction->inferPaidAmount(),
            'holdFlag' => $this->transaction->getHoldFlag(),
            'financialImpact' => $this->transaction->getImpact(),
            'transactionType' => $this->transaction->getTransactionType(),
            'transactionDescription' => $this->transaction->getTransactionDescription(),
            'establishment' => $this->transaction->getAcceptorName(),
            'establishmentCity' => $this->transaction->getAcceptorCity(),
            'establishmentCountry' => $this->transaction->getAcceptorCountry(),
            'establishmentTerminalId' => $this->transaction->getAcceptorTerminalId(),
            'establishmentPostalCode' => $this->transaction->getAcceptorPostal(),
            'mcc' => $this->transaction->getMcc(),
            'transactionId' => $this->transaction->getTransactionId(),
            'extra.cif' => $this->transaction->getExtraCif(),
            'extra.client_code' => $this->transaction->getExtraClientCode(),
            'extra.member_name' => $this->transaction->getExtraMemberName(),
            'extra.member_surname' => $this->transaction->getExtraMemberSurname(),
            'extra.product_name' => $this->transaction->getExtraProductName(),
            'extra.product_id' => $this->transaction->getExtraProductId(),
        ];
    }
}
