<?php

namespace App\Accounts\Application\Command;

use App\Accounts\Domain\Model\Account;
use App\Accounts\Infrastructure\Persistence\Doctrine\AccountRepositoryInterface;
use App\Shared\Domain\Bus\HandlerInterface;
use App\Shared\Domain\Contracts\UidGeneratorInterface;
use App\Shared\Infrastructure\Redis\CacheServiceInterface;
use Psr\Log\LoggerInterface;

readonly class AddAccountCommandHandler implements HandlerInterface
{
    public function __construct(
        private AccountRepositoryInterface $accountRepository,
        private UidGeneratorInterface $uidGenerator,
        private CacheServiceInterface $cache,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(AddAccountCommand $command): void
    {
        $account = Account::create(
            id: $this->uidGenerator->generate(),
            accountId: $command->accountId,
            accountName: $command->accountName,
            memberName: $command->memberName,
            memberSurname: $command->memberSurname,
            cif: $command->cif,
            mslCustomerId: $command->customerId,
            updateDate: null,
            organizationName: $command->organizationName,
            organizationId: $command->organizationId,
            clientCode: $command->clientCode,
            productName: $command->productName,
            productCode: $command->productCode,
            productId: $command->productId,
        );

        $exist = $this->accountRepository->findByAccountId($command->accountId);
        $key = 'account-details-'.$account->getAccountId();

        if ($exist) {
            $exist->updateWithFreshData($account);
            $this->accountRepository->save($exist);

            $this->logger->info(
                'Account {accountId} updated in master account registry',
                [
                    'accountId' => $account->getAccountId(),
                ]
            );

            $this->invalidateCache($key);

            return;
        }

        $this->logger->info(
            'Account {accountId} included in master account registry',
            [
                'accountId' => $account->getAccountId(),
            ]
        );

        $this->accountRepository->save($account);
        $this->cache->store($key, $account, $this->cache->getMonthTtl(), Account::class);
    }

    private function invalidateCache(string $key): void
    {
        if ($this->cache->keyExist($key)) {
            $this->cache->invalidate($key);
        }
    }
}
