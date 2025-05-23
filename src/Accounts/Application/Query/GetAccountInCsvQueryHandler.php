<?php

namespace App\Accounts\Application\Query;

use App\Accounts\Domain\Model\Account;
use App\Accounts\Infrastructure\Persistence\Doctrine\AccountRepositoryInterface;
use App\Shared\Domain\Bus\HandlerInterface;
use App\Shared\Infrastructure\File\Csv\CsvFileHandlerInterface;
use App\Shared\Infrastructure\Redis\CacheServiceInterface;
use Psr\Log\LoggerInterface;

readonly class GetAccountInCsvQueryHandler implements HandlerInterface
{
    public function __construct(
        private AccountRepositoryInterface $accountRepository,
        private CsvFileHandlerInterface $csvFileHandler,
        private LoggerInterface $logger,
        private CacheServiceInterface $cache
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(
        GetAccountInCsvQuery $query
    ): string {
        $key = md5('update-accounts-'.$query->updateDate->format('Y-m-d'));

        $cached = $this->cache->keyExist($key) ? $this->cache->getFromStore(
            $key,
            'string'
        ) : null;

        if (null !== $cached) {
            return $cached;
        }

        $accounts = $this->accountRepository->findAfterDate($query->updateDate);

        $csv = $this->csvFileHandler->buildCsv(
            array_map(
                static function (Account $account) {
                    return [
                        'accountId' => $account->getAccountId(),
                        'accountName' => $account->getAccountName(),
                        'memberName' => $account->getFullName(),
                        'mslCustomerId' => $account->getMslCustomerId(),
                        'organizationCif' => $account->getCif(),
                        'organizationName' => $account->getOrganizationName(),
                        'productCode' => $account->getProductCode(),
                        'clientCode' => $account->getClientCode(),
                        'productName' => $account->getProductName(),
                        'updatedAt' => $account->getUpdateDate()->format('Y-m-d H:i:s'),
                    ];
                },
                $accounts
            ),
            [
                'Account Id',
                'Account Name',
                'Member Name',
                'Msl Customer Id',
                'Organization Cif',
                'Organization Name',
                'Product Code',
                'Client Code',
                'Product Name',
                'Updated At',
            ]
        );

        $this->logger->info('Accounts update data requested and exported to csv', [
            'count' => count($accounts),
            'date' => $query->updateDate->format('Y-m-d'),
        ]);

        $this->cache->store(
            $key,
            $csv,
            $this->cache->getDefaultTtl(),
            'string'
        );

        return $csv;
    }
}
