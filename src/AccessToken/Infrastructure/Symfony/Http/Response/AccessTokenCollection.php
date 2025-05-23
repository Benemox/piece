<?php

namespace App\AccessToken\Infrastructure\Symfony\Http\Response;

use App\AccessToken\Domain\Model\AccessToken;
use App\Shared\Http\Pagination\PaginationResults;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'AccessTokenCollection',

    properties: [
        new OA\Property(property: 'pageNumber', description: 'Current page', type: 'integer', example: '1'),
        new OA\Property(property: 'totalPages', description: 'Total page', type: 'integer', example: '1'),
        new OA\Property(property: 'currentPageElements', description: 'Current elements per page', type: 'integer', example: '1'),
        new OA\Property(property: 'totalElements', description: 'Total elements', type: 'integer', example: '1'),
        new OA\Property(
            property: 'results',
            type: 'array',
            items: new OA\Items(ref: new Model(type: AccessTokenSchema::class))
        ),
    ]
)]
readonly class AccessTokenCollection implements \JsonSerializable
{
    public function __construct(private readonly PaginationResults $results)
    {
    }

    public function jsonSerialize(): array
    {
        return [
            'pageNumber' => $this->results->pageNumber,
            'totalPages' => $this->results->totalPages,
            'currentPageElements' => $this->results->currentPageElements,
            'totalElements' => $this->results->totalElements,
            'results' => array_map(
                static fn (AccessToken $user) => new AccessTokenSchema($user),
                $this->results->results
            ),
        ];
    }
}
