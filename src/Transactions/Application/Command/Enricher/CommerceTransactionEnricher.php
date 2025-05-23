<?php

namespace App\Transactions\Application\Command\Enricher;

use App\Commerces\Domain\Model\Commerce;
use App\Commerces\Infrastructure\Persistance\FucRepositoryInterface;
use App\Settings\Application\Services\CheckSettingServiceInterface;
use App\Shared\Infrastructure\Redis\CacheServiceInterface;
use App\Transactions\Application\Command\TransactionEnricherInterface;
use App\Transactions\Domain\Model\MslTransaction;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.transaction_enricher')]
readonly class CommerceTransactionEnricher implements TransactionEnricherInterface
{
    public function __construct(
        private FucRepositoryInterface $commerceRepository,
        private CacheServiceInterface $cache,
        private LoggerInterface $logger,
        private CheckSettingServiceInterface $checkGlobalSettingService
    ) {
    }

    public function enrich(MslTransaction $transaction): MslTransaction
    {
        if (false === $this->checkGlobalSettingService->isTransactionsCommerceEnrichmentEnabled()) {
            $this->logger->warning('Transactions commerce enrichment is disabled');

            return $transaction;
        }

        if (null === $transaction->getAcceptorId()) {
            return $transaction;
        }

        try {
            $commerceDetails = $this->getCommerceDetails($transaction->getAcceptorId());

            if (null === $commerceDetails) {
                $this->logger->info('Commerce not found to enrich transaction', [
                    'commerceCode' => $transaction->getAcceptorId(),
                    'transactionId' => $transaction->getTransactionId(),
                ]);

                return $transaction;
            }

            $transaction->enrichWithCommerceDetails(
                code: trim($commerceDetails->getCode() ?? ''),
                name: trim($commerceDetails->getName() ?? ''),
                csb: trim($commerceDetails->getCsb() ?? ''),
                cifNif: trim($commerceDetails->getCifNif() ?? ''),
                area: trim($commerceDetails->getArea() ?? ''),
                province: trim($commerceDetails->getProvince() ?? ''),
                address: trim($commerceDetails->getAddress() ?? ''),
                sectorInt: trim($commerceDetails->getSectorInt() ?? ''),
                sectorAct: trim($commerceDetails->getSectorAct() ?? '')
            );

            $this->logger->info('Successfully enriched transaction with commerce details', [
                'commerceCode' => $transaction->getAcceptorId(),
                'transactionId' => $transaction->getTransactionId(),
            ]);
        } catch (\Exception $e) {
            $this->logger->warning('Failed to enrich transaction with commerce details', [
                'commerceCode' => $transaction->getAcceptorId(),
                'transactionId' => $transaction->getTransactionId(),
                'error' => $e->getMessage(),
            ]);
        }

        return $transaction;
    }

    private function getCommerceDetails(string $commerceId): ?Commerce
    {
        $key = 'commerce-details-'.$commerceId;

        if ($this->cache->keyExist($key)) {
            $cache = $this->cache->getFromStore($key, 'array');

            return new Commerce($cache);
        }

        $commerceDetails = $this->commerceRepository->findByCommerceId($commerceId);

        try {
            if (null !== $commerceDetails) {
                $this->cache->store($key, $commerceDetails->getRawData(), $this->cache->getMonthTtl(), 'array');
            }
        } catch (\Exception $e) {
            $this->logger->warning('Failed to cache commerce details', [
                'commerceCode' => $commerceId,
                'error' => $e->getMessage(),
            ]);
        }

        return $commerceDetails;
    }
}
