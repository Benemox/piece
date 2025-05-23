<?php

namespace App\Shared\Infrastructure\Symfony\Messenger;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class FailureCatcherMiddleware implements MiddlewareInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    /**
     * @throws \Throwable
     */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        try {
            $returnedEnvelope = $stack->next()->handle($envelope, $stack);
        } catch (HandlerFailedException $handlerFailedException) {
            $this->logger->error('Command handler failed: '.$handlerFailedException->getMessage());
            throw $handlerFailedException->getPrevious(); // @phpstan-ignore-line
        }

        return $returnedEnvelope;
    }
}
