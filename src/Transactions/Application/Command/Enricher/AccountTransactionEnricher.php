<?php

namespace App\Transactions\Application\Command\Enricher;

use App\Accounts\Domain\Model\Account;
use App\Accounts\Infrastructure\Persistence\Doctrine\AccountRepositoryInterface;
use App\Settings\Application\Services\CheckSettingServiceInterface;
use App\Shared\Infrastructure\Redis\CacheServiceInterface;
use App\Transactions\Application\Command\TransactionEnricherInterface;
use App\Transactions\Domain\Model\MslTransaction;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.transaction_enricher')]
readonly class AccountTransactionEnricher implements TransactionEnricherInterface
{
    public function __construct(
        private AccountRepositoryInterface $accountsRepository,
        private CacheServiceInterface $cache,
        private LoggerInterface $logger,
        private CheckSettingServiceInterface $checkGlobalSettingService
    ) {
    }

    public function enrich(MslTransaction $transaction): MslTransaction
    {
        if (false === $this->checkGlobalSettingService->isTransactionsAccountEnrichmentEnabled()) {
            $this->logger->warning('Transactions account enrichment is disabled');

            return $transaction;
        }
        if (null === $transaction->getAccountId()) {
            return $transaction;
        }

        try {
            $accountDetails = $this->getAccountDetails($transaction->getAccountId());

            if (null === $accountDetails) {
                $this->logger->info('Account not found to enrich transaction', [
                    'accountId' => $transaction->getAccountId(),
                    'transactionId' => $transaction->getTransactionId(),
                ]);

                return $transaction;
            }

            $transaction->enrichWithAccountDetails(
                cif: $accountDetails->getCif(),
                clientCode: $accountDetails->getClientCode() ?? '',
                memberName: $accountDetails->getMemberName(),
                memberSurname: $accountDetails->getMemberSurname(),
                mslCustomerId: $accountDetails->getMslCustomerId(),
                organizationName: $accountDetails->getOrganizationName(),
                organizationId: $accountDetails->getOrganizationId(),
                productCode: $accountDetails->getProductCode(),
                productName: $accountDetails->getProductName(),
                productId: $accountDetails->getProductId(),
            );

            $this->logger->info('Successfully enriched transaction with account details', [
                'accountId' => $transaction->getAccountId(),
                'transactionId' => $transaction->getTransactionId(),
            ]);
        } catch (\Exception $e) {
            $this->logger->warning('Failed to enrich transaction with account details', [
                'accountId' => $transaction->getAccountId(),
                'transactionId' => $transaction->getTransactionId(),
                'error' => $e->getMessage(),
            ]);
        }

        return $transaction;
    }

    private function getAccountDetails(string $accountId): ?Account
    {
        $key = 'account-details-'.$accountId;

        if ($this->cache->keyExist($key) and ($this->cache->getFromStore($key, Account::class) instanceof Account)) {
            return $this->cache->getFromStore($key, Account::class);
        }

        $accountDetails = $this->accountsRepository->findByAccountId($accountId);

        if (null !== $accountDetails) {
            $this->cache->store($key, $accountDetails, $this->cache->getMonthTtl(), Account::class);
        }

        return $accountDetails;
    }
}
