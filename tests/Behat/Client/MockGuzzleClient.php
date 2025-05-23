<?php

namespace App\Tests\Behat\Client;

use App\Tests\Behat\Persistence\BehatVariablesDatabase;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class MockGuzzleClient implements ClientInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @throws MockClientException
     */
    public function send(RequestInterface $request, array $options = []): ResponseInterface
    {
        $hash = md5($request->getMethod().$request->getUri()->getPath());
        if ($exception = BehatVariablesDatabase::get('exception'.$hash)) {
            throw new MockClientException(json_encode(['message' => $exception]) ?: $exception);
        }

        BehatVariablesDatabase::set($hash.'sent', $request->getBody()->getContents());
        if (!$data = BehatVariablesDatabase::get($hash)) {
            throw new MockClientException(sprintf('No response for url %s with method %s', $request->getUri()->getPath(), $request->getMethod()));
        }

        if (!$body = json_encode($data)) {
            throw new MockClientException("Can't encode response to json");
        }
        $this->logger->debug(
            sprintf(
                "\n\n-------- Mock HTTP response -----------\n\n%s\n-------------------\n",
                $body
            )
        );

        return new Response(200, [], $body);
    }

    public function sendAsync(RequestInterface $request, array $options = []): PromiseInterface
    {
        return new RejectedPromise('Not implemented');
    }

    public function request($method, $uri, array $options = []): ResponseInterface
    {
        return $this->send(new Request($method, $uri));
    }

    public function requestAsync($method, $uri, array $options = []): PromiseInterface
    {
        return new RejectedPromise('Not implemented');
    }

    public function getConfig($option = null): null
    {
        return null;
    }
}
