<?php

namespace App\Shared\Infrastructure\Guzzle;

use App\Shared\Infrastructure\Sanitizer\LoggerWithSanitizer;
use App\Shared\Infrastructure\Sanitizer\Middleware\HttpLogMiddleware;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use Psr\Log\LoggerInterface;

readonly class HandlerStackFactory
{
    public function __construct(private LoggerInterface $logger, private array $guzzleSanitizeKeys)
    {
    }

    public function getRequestHandler(): HandlerStack
    {
        $handlerStack = HandlerStack::create();
        $handlerStack->setHandler(new CurlHandler());

        $logger = new LoggerWithSanitizer($this->logger, $this->guzzleSanitizeKeys);
        $handlerStack->push(new HttpLogMiddleware($logger), 'logger');

        return $handlerStack;
    }
}
