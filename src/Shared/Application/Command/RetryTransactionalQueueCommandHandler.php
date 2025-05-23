<?php

namespace App\Shared\Application\Command;

use App\Shared\Domain\Bus\DispatcherInterface;
use App\Shared\Domain\Bus\HandlerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;

class RetryTransactionalQueueCommandHandler implements HandlerInterface
{
    public function __construct(
        private ReceiverInterface $receiver,
        private DispatcherInterface $bus,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(RetryTransactionalQueueCommand $command): void
    {
        $messages = $this->receiver->get();

        foreach ($messages as $envelope) {
            $this->bus->dispatch($envelope->getMessage());

            $this->logger->warning('Message retried from Failed Queue', [
                'message' => get_class($envelope->getMessage()),
            ]);

            $this->receiver->ack($envelope);
        }
    }
}
