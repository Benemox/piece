<?php

namespace App\Transactions\Application\Event;

use App\Shared\Domain\Bus\DispatcherInterface;
use App\Shared\Domain\Bus\HandlerInterface;
use App\Transactions\Application\Command\SaveUpOneTransactionCommand;
use App\Transactions\Domain\Event\UpOneTransactionRaisedEvent;
use Psr\Log\LoggerInterface;

readonly class UpOneTransactionRaisedEventHandler implements HandlerInterface
{
    public function __construct(
        private DispatcherInterface $bus,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(UpOneTransactionRaisedEvent $event): void
    {
        $transaction = $event->castUpOneTransaction();

        $this->bus->dispatch(new SaveUpOneTransactionCommand($transaction));

        $this->logger->info('UpOne transaction {transactionId} raised and sent for persistance.', [
            'transactionId' => $transaction->getTransactionId(),
        ]);
    }
}
