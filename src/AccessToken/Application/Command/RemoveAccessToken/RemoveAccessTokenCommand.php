<?php

namespace App\AccessToken\Application\Command\RemoveAccessToken;

use App\Shared\Domain\Bus\CommandMessageInterface;

class RemoveAccessTokenCommand implements CommandMessageInterface
{
    public function __construct(
        public string $token
    ) {
    }
}
