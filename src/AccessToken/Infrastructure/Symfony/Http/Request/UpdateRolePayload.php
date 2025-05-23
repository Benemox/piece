<?php

namespace App\AccessToken\Infrastructure\Symfony\Http\Request;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateRolePayload
{
    public function __construct(
        #[OA\Property(type: 'string', enum: [
            'ROLE_CONSULTANT',
            'ROLE_ADMIN',
        ], example: 'ROLE_CONSULTANT', )]
        #[Assert\NotNull(message: 'validation.not_empty_or_null')]
        #[Assert\Type('string')]
        public string $role,
        #[Assert\Type('string')]
        #[Assert\NotNull(message: 'validation.not_empty_or_null')]
        public string $token
    ) {
    }
}
