<?php

namespace App\Shared\Infrastructure\Elastica;

use App\Transactions\Domain\Contracts\RegistryInterface;

interface ElasticaClientInterface
{
    public function addRegistry(
        string $index,
        RegistryInterface $registry,
        ?string $id,
        ?\DateTime $datetime = null
    ): void;

    public function criteriaSearch(
        string $index,
        string $field,
        string $value,
        \DateTime $from,
        \DateTime $to,
        array $criteria = []
    ): array;

    public function findFirstPreviousByCriteria(
        string $index,
        string $field,
        string $value,
        \DateTime $startDate,
        array $criteria = []
    ): array;

    public function simpleSearch(
        string $index,
        string $field,
        string $value,
    ): array;

    public function findBy(
        string $index,
        \DateTime $startDate,
        \DateTime $endDate,
        array $criteria = [],
        int $page = 1,
        int $size = 10,
    ): array;

    public function findByCount(
        string $index,
        \DateTime $startDate,
        \DateTime $endDate,
        array $criteria = []
    ): int;

    public function executeEsQlQuery(string $query, array $params = []): array;

    public function flushEvents(): void;
}
