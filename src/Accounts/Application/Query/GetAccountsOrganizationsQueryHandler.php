<?php

namespace App\Accounts\Application\Query;

use App\Accounts\Domain\Model\Organization;
use App\Accounts\Infrastructure\Persistence\Doctrine\AccountRepositoryInterface;
use App\Shared\Domain\Bus\HandlerInterface;

readonly class GetAccountsOrganizationsQueryHandler implements HandlerInterface
{
    public function __construct(
        private AccountRepositoryInterface $accountRepository
    ) {
    }

    /**
     * @return Organization[]
     */
    public function __invoke(GetAccountsOrganizationsQuery $query): array
    {
        return $this->accountRepository->getAccountsOrganizations();
    }
}
