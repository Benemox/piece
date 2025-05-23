<?php

namespace App\AccessToken\Infrastructure\Persistence\Doctrine;

use App\AccessToken\Application\Query\ListAccessTokenFilters;
use App\AccessToken\Domain\Model\AccessToken;
use App\Shared\Http\Pagination\Page;

interface AccessTokenRepositoryInterface
{
    public function findAll(): array;

    public function save(AccessToken $user): void;

    public function findById(string $token): ?AccessToken;

    /**
     * @return AccessToken[]
     */
    public function paginatedFilters(ListAccessTokenFilters $filters, Page $page): array;

    public function countByFilters(ListAccessTokenFilters $filters): int;

    public function remove(AccessToken $user): void;
}
