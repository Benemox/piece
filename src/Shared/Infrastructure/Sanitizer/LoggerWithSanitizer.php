<?php

namespace App\Shared\Infrastructure\Sanitizer;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class LoggerWithSanitizer extends AbstractLogger
{
    public function __construct(private readonly LoggerInterface $logger, private readonly array $fieldsToSanitize = [])
    {
    }

    public function log($level, string|\Stringable $message, array $context = []): void
    {
        if (!empty($this->fieldsToSanitize)) {
            $this->sanitizer($context, $this->fieldsToSanitize);
        }

        if (array_key_exists('request', $context)) {
            $request = $context['request'];
            $message = sprintf(
                "\n\n-------- Guzzle HTTP request -----------\n\n%s\n%s\n%s\n\n-------------------\n",
                $request['method'].' '.$request['url'].' '.$request['version'],
                $this->parseHeaders($request['headers'] ?? []),
                json_encode($request['body'] ?? [], flags: JSON_PRETTY_PRINT),
            );
            unset($context['request']);
        }
        if (array_key_exists('response', $context)) {
            $response = $context['response'];
            $message .= sprintf(
                "\n\n-------- Guzzle HTTP response -----------\n\n%s\n%s\n%s\n\n-------------------\n",
                $response['status_code'].' '.$response['message'],
                $this->parseHeaders($response['headers'] ?? []),
                json_encode($response['body'] ?? [], flags: JSON_PRETTY_PRINT),
            );
            unset($context['response']);
        }

        $this->logger->log($level, $message, $context);
    }

    private function parseHeaders(array $headers): string
    {
        $data = '';
        foreach ($headers as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            $data .= $key.' '.$value."\n";
        }

        return $data;
    }

    private function sanitizer(array &$data, array $fields): void
    {
        $dot = new DotArray($data);
        foreach ($fields as $key => $format) {
            if (is_numeric($key)) {
                $key = $format;
                $format = ValueSanitizer::DEFAULT;
            }

            if (!$value = $dot->get($key)) {
                continue;
            }
            $dot->set($key, ValueSanitizer::sanitize($format, $value));
        }
    }
}
