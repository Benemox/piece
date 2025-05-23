<?php

namespace App\AccessToken\Application\Query;

use App\AccessToken\Infrastructure\Persistence\Doctrine\AccessTokenRepositoryInterface;
use App\AccessToken\Infrastructure\Symfony\Http\Response\AccessTokenCollection;
use App\Shared\Domain\Bus\HandlerInterface;
use App\Shared\Http\Pagination\PaginationResults;

readonly class ListAccessTokenQueryHandler implements HandlerInterface
{
    public function __construct(
        private AccessTokenRepositoryInterface $accessTokenRepository
    ) {
    }

    public function __invoke(ListAccessTokenQuery $query): AccessTokenCollection
    {
        $filters = $query->getFilters();
        $total = $this->accessTokenRepository->countByFilters($filters);
        $accessTokens = $this->accessTokenRepository->paginatedFilters($filters, $query->page);

        $paginationResults = new PaginationResults($query->page, $total, $accessTokens);

        return new AccessTokenCollection($paginationResults);
    }
}
