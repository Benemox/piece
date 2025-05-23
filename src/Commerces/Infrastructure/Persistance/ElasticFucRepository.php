<?php

namespace App\Commerces\Infrastructure\Persistance;

use App\Commerces\Domain\Model\Commerce;
use App\Shared\Infrastructure\Elastica\ElasticaClientInterface;

readonly class ElasticFucRepository implements FucRepositoryInterface
{
    public function __construct(
        private ElasticaClientInterface $elasticaClient,
        private readonly string $fucIndex,
    ) {
    }

    public function findByCommerceId(string $commerceId): ?Commerce
    {
        $result = $this->elasticaClient->simpleSearch(
            $this->fucIndex,
            'field.code',
            $commerceId,
        );

        if (0 === count($result)) {
            return null;
        } else {
            return new Commerce($result[0]['field']);
        }
    }
}
