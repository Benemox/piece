<?php

namespace App\AccessToken\Infrastructure\Symfony\Http\Response;

use App\AccessToken\Domain\Model\AccessToken;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'AccessTokenSchema',
    properties: [
        new OA\Property(
            property: 'token',
            type: 'text',
            example: 1
        ),
        new OA\Property(
            property: 'role',
            type: 'string',
            example: 'ROLE_ADMIN'
        ),
    ]
)]
readonly class AccessTokenSchema implements \JsonSerializable
{
    public function __construct(private AccessToken $user)
    {
    }

    public function jsonSerialize(): array
    {
        return [
            'token' => $this->user->getToken(),
            'role' => $this->user->getRole(),
        ];
    }
}
