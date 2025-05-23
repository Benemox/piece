<?php

namespace App\Shared\Infrastructure\Guzzle;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

readonly class ClientFactory implements ClientFactoryInterface
{
    public function __construct(private HandlerStackFactory $handlerStackFactory)
    {
    }

    public function getClient(array $options = []): ClientInterface
    {
        $options['handler'] = $this->handlerStackFactory->getRequestHandler();

        return new Client($options);
    }
}
