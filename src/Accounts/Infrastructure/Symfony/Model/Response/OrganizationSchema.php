<?php

namespace App\Accounts\Infrastructure\Symfony\Model\Response;

use App\Accounts\Domain\Model\Organization;
use OpenApi\Attributes as OA;

#[OA\Schema(
    properties: [
        new OA\Property(
            property: 'cif',
            type: 'string',
            example: '12345678A'
        ),
        new OA\Property(
            property: 'name',
            type: 'string',
            example: 'INFORMA DyB S.A.U'
        ),
        new OA\Property(
            property: 'organizationId',
            type: 'string',
            format: 'uuid',
            example: '8d2b3c4d-5e6f-7g8h-9i0j-1k2l3m4n4o8k'
        ),
    ]
)]
readonly class OrganizationSchema implements \JsonSerializable
{
    public function __construct(
        private Organization $organization
    ) {
    }

    /**
     * @throws \Exception
     */
    public function jsonSerialize(): array
    {
        return [
            'cif' => $this->organization->getCif(),
            'name' => $this->organization->getName(),
            'organizationId' => $this->organization->getOrganizationId(),
        ];
    }
}
