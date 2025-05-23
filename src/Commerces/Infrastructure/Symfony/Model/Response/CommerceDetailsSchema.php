<?php

namespace App\Commerces\Infrastructure\Symfony\Model\Response;

use App\Commerces\Domain\Model\Commerce;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'Commerce Details',
    properties: [
        new OA\Property(property: 'code', type: 'string'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'csb', type: 'string'),
        new OA\Property(property: 'cif_nif', type: 'string'),
        new OA\Property(property: 'area', type: 'string'),
        new OA\Property(property: 'province', type: 'string'),
        new OA\Property(property: 'address', type: 'string'),
        new OA\Property(property: 'sector_int', type: 'string'),
        new OA\Property(property: 'sector_act', type: 'string'),
    ]
)]
readonly class CommerceDetailsSchema implements \JsonSerializable
{
    public function __construct(
        private Commerce $commerce
    ) {
    }

    public function jsonSerialize(): mixed
    {
        return [
            'code' => $this->commerce->getCode(),
            'name' => $this->commerce->getName(),
            'csb' => $this->commerce->getCsb(),
            'cif_nif' => $this->commerce->getCifNif(),
            'area' => $this->commerce->getArea(),
            'province' => $this->commerce->getProvince(),
            'address' => $this->commerce->getAddress(),
            'sector_int' => $this->commerce->getSectorInt(),
            'sector_act' => $this->commerce->getSectorAct(),
        ];
    }
}
