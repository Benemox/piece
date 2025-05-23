<?php

namespace App\Accounts\Application\Event;

use App\Accounts\Application\Command\AddAccountCommand;
use App\Shared\Domain\Bus\DispatcherInterface;
use App\Shared\Domain\Bus\HandlerInterface;
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

        $this->logger->info(
            'UpOne transaction {transactionId} was made. Proceed to update the account {accountId} in the master registry',
            [
                'transactionId' => $transaction->getTransactionId(),
                'accountId' => $transaction->getAccountId(),
            ]
        );

        assert(null !== $transaction->getAccountId());
        assert(null !== $transaction->getAccountName());
        assert(null !== $transaction->getCustomerName());
        assert(null !== $transaction->getCustomerSurname());
        assert(null !== $transaction->getCustomerId());
        assert(null !== $transaction->getCif());
        assert(null !== $transaction->getOrganizationName());
        assert(null !== $transaction->getOrganizationId());
        assert(null !== $transaction->getProductId());
        assert(null !== $transaction->getProductCode());
        assert(null !== $transaction->getProductName());

        $this->bus->dispatch(
            new AddAccountCommand(
                $transaction->getAccountId(),
                $transaction->getAccountName(),
                $transaction->getCustomerName(),
                $transaction->getCustomerSurname(),
                $transaction->getCif(),
                $transaction->getOrganizationName(),
                $transaction->getOrganizationId(),
                $transaction->getProductName(),
                $transaction->getProductCode(),
                $transaction->getProductId(),
                $transaction->getCustomerId(),
                $transaction->getClientCode(),
            )
        );
    }
}
