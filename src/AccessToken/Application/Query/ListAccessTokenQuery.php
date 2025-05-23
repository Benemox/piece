<?php

namespace App\AccessToken\Application\Query;

use App\AccessToken\Domain\Contracts\RoleInterface;
use App\Shared\Domain\Bus\QueryMessageInterface;
use App\Shared\Http\Pagination\Page;

class ListAccessTokenQuery implements QueryMessageInterface
{
    public function __construct(
        public ?RoleInterface $role,
        public Page $page,
    ) {
    }

    public function getFilters(): ListAccessTokenFilters
    {
        $filters = new ListAccessTokenFilters();

        if ($this->role) {
            $filters->withRole($this->role);
        }

        return $filters;
    }
}
