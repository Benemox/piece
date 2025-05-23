<?php

namespace App\Shared\Infrastructure\Elastica;

use App\Transactions\Domain\Contracts\RegistryInterface;
use Elastica\Client;
use Elastica\Document;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\MatchAll;
use Elastica\Query\MatchQuery;
use Elastica\Query\Range;
use Elastica\Query\Terms;

class ElasticaClient implements ElasticaClientInterface
{
    public const PATH = '/{index_name}/_search?pretty';

    public const DATE_FORMAT_START = 'Y-m-d\T00:00:00';
    public const DATE_FORMAT_END = 'Y-m-d\T23:59:59';

    public function __construct(
        private Client $elasticaClient,
        private ElasticaEventListener $eventListener
    ) {
    }

    /**
     * @throws \Exception
     */
    public function addRegistry(
        string $index,
        RegistryInterface $registry,
        ?string $id,
        ?\DateTime $datetime = null
    ): void {
        $now = new \DateTime();

        $registryArray = $registry->getRawData();

        $registryArray['datetime'] = null !== $datetime ? $datetime->format(\DateTimeInterface::ATOM) : $now->format(
            \DateTimeInterface::ATOM
        );

        $index = $this->elasticaClient->getIndex($index);
        $document = new Document($id, $registryArray);
        $index->addDocument($document);
        $index->refresh();
    }

    public function simpleSearch(string $index, string $field, string $value): array
    {
        $index = $this->elasticaClient->getIndex($index);

        $boolQuery = new BoolQuery();
        $boolQuery->addMust(new MatchQuery($field, $value));

        $query = new Query($boolQuery);

        $resultSet = $index->search($query);
        $results = [];

        foreach ($resultSet->getResults() as $result) {
            $results[] = $result->getData();
        }

        return $results;
    }

    public function criteriaSearch(
        string $index,
        string $field,
        string $value,
        \DateTime $from,
        \DateTime $to,
        array $criteria = []
    ): array {
        $indexObj = $this->elasticaClient->getIndex($index);

        $boolQuery = new BoolQuery();
        $boolQuery->addMust(new MatchQuery($field, $value));

        foreach ($criteria as $field => $fieldValue) {
            if (is_array($fieldValue)) {
                $boolQuery->addMust(new Terms($field, $fieldValue));
                $boolQuery->addMust(new MatchAll());
                continue;
            }
            $boolQuery->addMust(new MatchQuery($field, $fieldValue));
        }

        // Add date range query if provided
        $rangeQuery = new Range('datetime', [
            'gte' => $from->format(self::DATE_FORMAT_START),
            'lte' => $to->format(self::DATE_FORMAT_END),
        ]);
        $boolQuery->addFilter($rangeQuery);

        $query = new Query($boolQuery);

        $query->addSort(['datetime' => ['order' => 'desc']]);

        $query->setSize(10000);

        $resultSet = $indexObj->search($query);
        $results = [];

        foreach ($resultSet->getResults() as $result) {
            $results[] = $result->getData();
        }

        return $results;
    }

    /**
     * Search documents with optional field value, date range, and pagination.
     * Return the previous document before the startDate that match criteria.
     *
     * @return array search results
     */
    public function findFirstPreviousByCriteria(
        string $index,
        string $field,
        string $value,
        \DateTime $startDate,
        array $criteria = []
    ): array {
        $index = $this->elasticaClient->getIndex($index);

        $boolQuery = new BoolQuery();
        $boolQuery->addMust(new MatchQuery($field, $value));

        foreach ($criteria as $field => $fieldValue) {
            if (is_array($fieldValue)) {
                $boolQuery->addMust(new Terms($field, $fieldValue));
                $boolQuery->addMust(new MatchAll());
                continue;
            }
            $boolQuery->addMust(new MatchQuery($field, $fieldValue));
        }

        // Add date range query if provided
        $rangeQuery = new Range('datetime', [
            'lt' => $startDate->format(self::DATE_FORMAT_START),
        ]);
        $boolQuery->addFilter($rangeQuery);

        $query = new Query($boolQuery);

        $query->addSort(['datetime' => ['order' => 'desc']]);

        $query->setSize(1);

        $resultSet = $index->search($query);
        $results = [];

        foreach ($resultSet->getResults() as $result) {
            $results[] = $result->getData();
        }

        return $results;
    }

    public function executeEsQlQuery(string $query, array $params = []): array
    {
        $elastic = $this->elasticaClient;

        $body = [
            'query' => $query,
            'params' => $params,
        ];

        $data = $elastic->request('_query', 'POST', $body);
        $response = [];

        $columns = array_key_exists('columns', $data->getData()) ? $data->getData()['columns'] : [];
        $values = array_key_exists(0, $data->getData()['values']) ? $data->getData()['values'] : [];

        foreach ($values as $value) {
            $response[] = array_combine(array_column($columns, 'name'), $value);
        }

        return $response;
    }

    /**
     * Search documents with optional field value, date range, and pagination.
     *
     * @return array search results
     */
    public function findBy(
        string $index,
        \DateTime $startDate,
        \DateTime $endDate,
        array $criteria = [],
        int $page = 1,
        int $size = 10
    ): array {
        $elasticIndex = $this->elasticaClient->getIndex($index);

        $boolQuery = $this->buildQueryAndCriteria($criteria);

        // Add date range query if provided
        $rangeQuery = new Range('datetime', [
            'gte' => $startDate->format(self::DATE_FORMAT_START),
            'lte' => $endDate->format(self::DATE_FORMAT_END),
        ]);
        $boolQuery->addFilter($rangeQuery);

        $from = ($page - 1) * $size;
        $query = new Query($boolQuery);
        $query->setFrom($from);
        $query->setSize($size);

        $query->addSort(['datetime' => ['order' => 'desc']]);

        $resultSet = $elasticIndex->search($query);

        $results = [];
        foreach ($resultSet->getResults() as $result) {
            $results[] = $result->getData();
        }

        return $results;
    }

    public function findByCount(
        string $index,
        \DateTime $startDate,
        \DateTime $endDate,
        array $criteria = []
    ): int {
        $elasticIndex = $this->elasticaClient->getIndex($index);

        $boolQuery = $this->buildQueryAndCriteria($criteria);

        // Add date range query if provided
        $rangeQuery = new Range('datetime', [
            'gte' => $startDate->format(self::DATE_FORMAT_START),
            'lte' => $endDate->format(self::DATE_FORMAT_END),
        ]);
        $boolQuery->addFilter($rangeQuery);

        $query = new Query($boolQuery);

        $resultSet = $elasticIndex->search($query);

        return $resultSet->getTotalHits();
    }

    private function buildQueryAndCriteria(array $criteria): BoolQuery
    {
        $boolQuery = new BoolQuery();

        foreach ($criteria as $criteriaField => $criteriaValue) {
            if ('card_ids' === $criteriaField && is_array($criteriaValue)) {
                $boolQuery->addMust(new Terms('card_id', $criteriaValue));
                $boolQuery->addMust(new MatchAll());
                continue;
            }
            if ('account_ids' === $criteriaField && is_array($criteriaValue)) {
                $boolQuery->addMust(new Terms('account', $criteriaValue));
                $boolQuery->addMust(new MatchAll());
                continue;
            }

            $boolQuery->addMust(new MatchQuery($criteriaField, $criteriaValue));
        }

        return $boolQuery;
    }

    public function flushEvents(): void
    {
        $this->eventListener->onFlush();
    }
}
