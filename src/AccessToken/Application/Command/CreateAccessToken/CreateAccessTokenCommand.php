<?php

namespace App\AccessToken\Application\Command\CreateAccessToken;

use App\AccessToken\Domain\Contracts\RoleInterface;
use App\Shared\Domain\Bus\CommandMessageInterface;

readonly class CreateAccessTokenCommand implements CommandMessageInterface
{
    public function __construct(
        public RoleInterface $role
    ) {
    }
}
