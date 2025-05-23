<?php

namespace App\Transactions\Application\Command;

use App\Shared\Domain\Bus\HandlerInterface;
use App\Transactions\Infrastructure\Persistance\UpOneTransactionsRepositoryInterface;
use Psr\Log\LoggerInterface;

readonly class SaveUpOneTransactionCommandHandler implements HandlerInterface
{
    public function __construct(
        private UpOneTransactionsRepositoryInterface $transactionsRepository,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(SaveUpOneTransactionCommand $command): void
    {
        $this->transactionsRepository->save($command->transaction);

        $this->logger->info('UpOne transaction {transactionId} saved.', [
            'transactionId' => $command->transaction->getTransactionId(),
        ]);
    }
}
