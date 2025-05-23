<?php

namespace App\Tests\Behat\Client;

use App\Shared\Infrastructure\Guzzle\ClientFactoryInterface;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;

class MockClientFactory implements ClientFactoryInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getClient(array $options = []): ClientInterface
    {
        return new MockGuzzleClient($this->logger);
    }
}
