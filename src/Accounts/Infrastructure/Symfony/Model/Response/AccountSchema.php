<?php

namespace App\Accounts\Infrastructure\Symfony\Model\Response;

use App\Accounts\Domain\Model\Account;
use OpenApi\Attributes as OA;

#[OA\Schema(
    properties: [
        new OA\Property(
            property: 'accountId',
            type: 'string'
        ),
        new OA\Property(
            property: 'accountName',
            type: 'string'
        ),
        new OA\Property(
            property: 'memberName',
            type: 'string'
        ),
        new OA\Property(
            property: 'memberSurname',
            type: 'string'
        ),
        new OA\Property(
            property: 'cif',
            type: 'string'
        ),
        new OA\Property(
            property: 'clientCode',
            type: 'string'
        ),
        new OA\Property(
            property: 'productName',
            type: 'string'
        ),
        new OA\Property(
            property: 'productId',
            type: 'string'
        ),
        new OA\Property(
            property: 'productCode',
            type: 'string'
        ),
        new OA\Property(
            property: 'organizationName',
            type: 'string'
        ),
        new OA\Property(
            property: 'organizationId',
            type: 'string'
        ),
        new OA\Property(
            property: 'updated',
            type: 'string'
        ),
    ]
)]
readonly class AccountSchema implements \JsonSerializable
{
    public function __construct(
        private Account $account
    ) {
    }

    /**
     * @throws \Exception
     */
    public function jsonSerialize(): array
    {
        return [
            'accountId' => $this->account->getAccountId(),
            'accountName' => $this->account->getAccountName(),
            'memberName' => $this->account->getMemberName(),
            'memberSurname' => $this->account->getMemberSurname(),
            'mslCustomerId' => $this->account->getMslCustomerId(),
            'cif' => $this->account->getCif(),
            'clientCode' => $this->account->getClientCode(),
            'productName' => $this->account->getProductName(),
            'productId' => $this->account->getProductId(),
            'productCode' => $this->account->getProductCode(),
            'organizationName' => $this->account->getOrganizationName(),
            'organizationId' => $this->account->getOrganizationId(),
            'updated' => $this->account->getUpdateDate()->format(Account::DATE_FORMAT),
        ];
    }
}
