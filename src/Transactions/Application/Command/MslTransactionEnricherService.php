<?php

namespace App\Transactions\Application\Command;

use App\Transactions\Domain\Model\MslTransaction;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

readonly class MslTransactionEnricherService implements MslTransactionEnricherServiceInterface
{
    public function __construct(
        #[TaggedIterator('app.transaction_enricher')]
        /** @var iterable<TransactionEnricherInterface> */
        private iterable $enrichers
    ) {
    }

    public function enrich(MslTransaction $transaction): MslTransaction
    {
        foreach ($this->enrichers as $enricher) {
            $transaction = $enricher->enrich($transaction);
        }

        return $transaction;
    }
}
