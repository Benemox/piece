<?php

namespace App\Shared\Infrastructure\Sanitizer\Middleware;

use GuzzleHttp\TransferStats;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class HttpLog
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function log(
        RequestInterface $request,
        ?ResponseInterface $response = null,
        ?\Throwable $exception = null,
        ?TransferStats $stats = null,
    ): void {
        $this->logRequest($request);

        if (null !== $stats) {
            $this->logStats($stats);
        }

        if (null !== $response) {
            $this->logResponse($response);
        } else {
            $this->logException($exception);
        }
    }

    private function logRequest(RequestInterface $request): void
    {
        $this->logger->info('Guzzle HTTP Request', $this->getRequestContext($request));
    }

    private function logResponse(ResponseInterface $response): void
    {
        $this->logger->info('Guzzle HTTP Response', $this->getResponseContext($response));
    }

    private function logException(?\Throwable $exception): void
    {
        if (null === $exception) {
            return;
        }

        $this->logger->error('Guzzle HTTP Exception', ['exception' => $exception]);
    }

    private function logStats(TransferStats $stats): void
    {
        $this->logger->debug('Guzzle HTTP statistics', [
            'time' => $stats->getTransferTime(),
            'uri' => $stats->getEffectiveUri(),
        ]);
    }

    private function getRequestContext(RequestInterface $request): array
    {
        return [
            'request' => array_filter([
                'url' => $request->getUri()->__toString(),
                'body' => $request->getBody()->getSize() > 0 ? $this->formatBody($request) : null,
                'method' => $request->getMethod(),
                'headers' => $request->getHeaders(),
                'version' => 'HTTP/'.$request->getProtocolVersion(),
            ]),
        ];
    }

    private function getResponseContext(ResponseInterface $response): array
    {
        return [
            'response' => array_filter([
                'status_code' => $response->getStatusCode(),
                'body' => $response->getBody()->getSize() > 0 ? $this->formatBody($response) : null,
                'headers' => $response->getHeaders(),
                'version' => 'HTTP/'.$response->getProtocolVersion(),
                'message' => $response->getReasonPhrase(),
            ]),
        ];
    }

    private function formatBody(MessageInterface $response): array|string
    {
        $body = $response->getBody()->__toString();

        $json = json_decode($body, true);

        if (!empty($json)) {
            return $json;
        }

        return 'Could not json decode body';
    }
}
