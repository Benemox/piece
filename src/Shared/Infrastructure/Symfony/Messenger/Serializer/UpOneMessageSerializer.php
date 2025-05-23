<?php

namespace App\Shared\Infrastructure\Symfony\Messenger\Serializer;

use App\Transactions\Domain\Event\UpOneTransactionRaisedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

readonly class UpOneMessageSerializer implements SerializerInterface
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws \JsonException
     */
    public function decode(array $encodedEnvelope): Envelope
    {
        $body = $encodedEnvelope['body'];
        $headers = $encodedEnvelope['headers'];

        $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        if (JSON_ERROR_NONE !== json_last_error()) {
            $this->logger->error('Invalid JSON: '.json_last_error_msg());
        }

        $message = new UpOneTransactionRaisedEvent($data['data'], $data['type']);

        $stamps = [];
        if (!empty($headers['stamps'])) {
            $decodedStamps = is_string($headers['stamps']) ? json_decode($headers['stamps'], true) : $headers['stamps'];

            if (is_array($decodedStamps)) {
                foreach ($decodedStamps as $stampClass => $stampDataArray) {
                    if (class_exists($stampClass) && is_subclass_of($stampClass, \Symfony\Component\Messenger\Stamp\StampInterface::class)) {
                        foreach ($stampDataArray as $stampData) {
                            $stamps[] = new $stampClass(...array_values($stampData));
                        }
                    } else {
                        $this->logger->warning("Unknown stamp class: {$stampClass}");
                    }
                }
            }
        }

        return new Envelope($message, $stamps);
    }

    public function encode(Envelope $envelope): array
    {
        $message = $envelope->getMessage();

        if (!$message instanceof UpOneTransactionRaisedEvent) {
            $this->logger->error('Invalid message type');

            return [];
        }

        return [
            'body' => json_encode([
                'type' => $message->type,
                'data' => $message->data,
            ]),
            'headers' => [
                'stamps' => json_encode(
                    array_map(fn ($stamps) => array_map(fn ($stamp) => (array) $stamp, $stamps), $envelope->all())
                ),
            ],
        ];
    }
}
