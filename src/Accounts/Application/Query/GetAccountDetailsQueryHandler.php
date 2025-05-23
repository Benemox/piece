<?php

namespace App\Accounts\Application\Query;

use App\Accounts\Domain\Exception\AccountException;
use App\Accounts\Domain\Model\Account;
use App\Accounts\Infrastructure\Persistence\Doctrine\AccountRepositoryInterface;
use App\Accounts\Infrastructure\Symfony\Model\Response\AccountSchema;
use App\Shared\Domain\Bus\HandlerInterface;
use App\Shared\Infrastructure\Redis\CacheServiceInterface;

readonly class GetAccountDetailsQueryHandler implements HandlerInterface
{
    public function __construct(
        private AccountRepositoryInterface $accountRepository,
        private CacheServiceInterface $cache
    ) {
    }

    public function __invoke(GetAccountDetailsQuery $query): AccountSchema
    {
        $key = md5('account-details:'.$query->accountId);

        $cached = $this->cache->keyExist($key) ? $this->cache->getFromStore(
            $key,
            Account::class
        ) : null;

        if ($cached instanceof Account) {
            return new AccountSchema($cached);
        }

        $exist = $this->accountRepository->findByAccountId($query->accountId);

        if (!$exist) {
            throw AccountException::accountNotFound();
        }

        $this->cache->store(
            $key,
            $exist,
            $this->cache->getDefaultTtl(),
            Account::class
        );

        return new AccountSchema($exist);
    }
}
