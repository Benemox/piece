<?php

namespace App\Transactions\Application\Command;

use App\Shared\Domain\Bus\HandlerInterface;
use App\Transactions\Domain\Model\MslTransaction;
use App\Transactions\Infrastructure\Persistance\MslTransactionsRepositoryInterface;
use Psr\Log\LoggerInterface;

readonly class SaveMslTransactionCommandHandler implements HandlerInterface
{
    public function __construct(
        private MslTransactionsRepositoryInterface $transactionsRepository,
        private MslTransactionEnricherService $mslTransactionEnricher,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(SaveMslTransactionCommand $command): void
    {
        $transaction = new MslTransaction(
            $command->transactionData
        );

        $enriched = $this->mslTransactionEnricher->enrich($transaction);

        $this->transactionsRepository->save(
            $enriched
        );

        $this->logger->info('Msl transaction {transactionId} saved.', [
            'transactionId' => $transaction->getTransactionId(),
        ]);
    }
}
