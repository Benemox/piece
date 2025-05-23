<?php

namespace App\Tests\Behat;

use Behat\Behat\Context\Context;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use PHPUnit\Framework\Assert;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ElasticContext implements Context
{
    private Client $elasticsearch;

    public function __construct(
        #[Autowire(env: 'ELASTICSEARCH_HOST')]
        private readonly string $elasticHost = 'elasticsearch',
        #[Autowire(env: 'ELASTICSEARCH_PORT')]
        private readonly string $elasticPort = '9200',
        private readonly string $elasticUsername = 'elastic',
        private readonly string $elasticPassword = 'changeme'
    ) {
        $this->elasticsearch = ClientBuilder::create()
            ->setHosts([$this->elasticHost.':'.$this->elasticPort])
            ->setBasicAuthentication(
                $this->elasticUsername,
                $this->elasticPassword
            )
            ->build();
    }

    /**
     * @Then the Elasticsearch index :index should contain :value for :key
     *
     * @throws \Exception
     */
    public function theElasticsearchIndexShouldContain(string $index, string $value, string $key): void
    {
        $response = $this->elasticsearch->search([
            'index' => $index,
            'body' => [
                'query' => [
                    'match' => [
                        $key => $value,
                    ],
                ],
            ],
        ]);

        Assert::assertNotNull($response['hits']['hits']);
        Assert::assertArrayHasKey($key, $response['hits']['hits'][0]['_source']);
        Assert::assertEquals($value, $response['hits']['hits'][0]['_source'][$key]);
    }
}
