<?php

namespace App\AccessToken\Infrastructure\Symfony\Http\Request;

use Symfony\Component\Validator\Constraints as Assert;

class RemoveTokenPayload
{
    public function __construct(
        #[Assert\Type('string')]
        #[Assert\NotNull(message: 'validation.not_empty_or_null')]
        public string $token
    ) {
    }
}
