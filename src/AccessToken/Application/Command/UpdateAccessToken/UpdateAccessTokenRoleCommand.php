<?php

namespace App\AccessToken\Application\Command\UpdateAccessToken;

use App\AccessToken\Domain\Model\Role;
use App\Shared\Domain\Bus\CommandMessageInterface;

class UpdateAccessTokenRoleCommand implements CommandMessageInterface
{
    public function __construct(
        public string $token,
        public Role $role,
    ) {
    }
}
