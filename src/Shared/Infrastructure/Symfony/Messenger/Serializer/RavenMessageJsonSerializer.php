<?php

namespace App\Shared\Infrastructure\Symfony\Messenger\Serializer;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Serializer\SerializerInterface as SymfonySerializer;

class RavenMessageJsonSerializer implements SerializerInterface
{
    private SymfonySerializer $serializer;

    public function __construct(
        SymfonySerializer $serializer,
    ) {
        $this->serializer = $serializer;
    }

    public function decode(array $encodedEnvelope): Envelope
    {
        if (!isset($encodedEnvelope['body'])) {
            throw new MessageDecodingFailedException('No body found in the encoded message.');
        }

        $message = $this->serializer->deserialize($encodedEnvelope['body'], 'json', 'json');

        return new Envelope($message);
    }

    public function encode(Envelope $envelope): array
    {
        $message = $envelope->getMessage();

        // Serialize the message object to JSON
        $body = $this->serializer->serialize($message, 'json');

        return [
            'body' => $body,
            'headers' => [
                'type' => get_class($message),
            ],
        ];
    }
}
