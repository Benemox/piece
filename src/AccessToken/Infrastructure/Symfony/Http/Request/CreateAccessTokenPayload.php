<?php

namespace App\AccessToken\Infrastructure\Symfony\Http\Request;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

class CreateAccessTokenPayload
{
    public function __construct(
        #[OA\Property(description: 'Role', type: 'string', enum: ['ROLE_CONSULTANT', 'ROLE_ADMIN'], example: 'ROLE_CONSULTANT')]
        #[Assert\NotNull(message: 'validation.not_empty_or_null')]
        #[Assert\Type('string')]
        public string $role,
    ) {
    }
}
