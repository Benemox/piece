<?php

namespace App\Shared\Infrastructure\Symfony\Messenger;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Messenger\Stamp\SentStamp;
use Symfony\Component\Messenger\Stamp\SentToFailureTransportStamp;

readonly class AuditMessagesMiddleware implements MiddlewareInterface
{
    public function __construct(
        private LoggerInterface $messengerAuditLogger
    ) {
    }

    /**
     * @throws \Throwable
     */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if (null === $envelope->last(UniqueIdStamp::class)) {
            $envelope = $envelope->with(new UniqueIdStamp());
        }
        /** @var UniqueIdStamp $stamp */
        $stamp = $envelope->last(UniqueIdStamp::class);

        $context = [
            'id' => $stamp->getUniqueId(),
            'class' => get_class($envelope->getMessage()),
        ];

        $returnedEnvelope = $stack->next()->handle($envelope, $stack);

        if ($returnedEnvelope->last(ReceivedStamp::class)) {
            $this->messengerAuditLogger->info('[{id}] Received {class}', $context);
        } elseif ($envelope->last(SentStamp::class)) {
            $this->messengerAuditLogger->info('[{id}] Sent {class}', $context);
        } elseif ($returnedEnvelope->last(SentToFailureTransportStamp::class)) {
            $this->messengerAuditLogger->warning('[{id}] Sent to failure transport {class}', $context);
        } else {
            $this->messengerAuditLogger->info('[{id}] Handling sync {class}', $context);
        }

        return $returnedEnvelope;
    }
}
