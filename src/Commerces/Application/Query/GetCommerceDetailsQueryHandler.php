<?php

namespace App\Commerces\Application\Query;

use App\Commerces\Domain\Exception\CommercesException;
use App\Commerces\Domain\Model\Commerce;
use App\Commerces\Infrastructure\Persistance\FucRepositoryInterface;
use App\Commerces\Infrastructure\Symfony\Model\Response\CommerceDetailsSchema;
use App\Shared\Domain\Bus\HandlerInterface;
use App\Shared\Infrastructure\Redis\CacheServiceInterface;

readonly class GetCommerceDetailsQueryHandler implements HandlerInterface
{
    public function __construct(
        private FucRepositoryInterface $commercesRepository,
        private CacheServiceInterface $cache
    ) {
    }

    public function __invoke(GetCommerceDetailsQuery $query): CommerceDetailsSchema
    {
        $cached = $this->cache->keyExist($query->commerceId) ? $this->cache->getFromStore(
            $query->commerceId,
            Commerce::class
        ) : null;

        if ($cached instanceof Commerce) {
            return new CommerceDetailsSchema($cached);
        }

        $existCommerce = $this->commercesRepository->findByCommerceId($query->commerceId);

        if (!$existCommerce) {
            throw CommercesException::commerceNotFound();
        }

        $this->cache->store($query->commerceId, $existCommerce, $this->cache->getDefaultTtl(), Commerce::class);

        return new CommerceDetailsSchema($existCommerce);
    }
}
