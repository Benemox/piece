<?php

namespace App\Shared\Infrastructure\Guzzle;

use GuzzleHttp\ClientInterface;

interface ClientFactoryInterface
{
    public function getClient(array $options = []): ClientInterface;
}
