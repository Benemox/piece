<?php

namespace App\Tests\Unit\Accounts\Query;

use App\Accounts\Application\Query\GetAccountDetailsQuery;
use App\Accounts\Application\Query\GetAccountDetailsQueryHandler;
use App\Accounts\Domain\Exception\AccountException;
use App\Accounts\Domain\Model\Account;
use App\Accounts\Infrastructure\Persistence\Doctrine\AccountRepositoryInterface;
use App\Accounts\Infrastructure\Symfony\Model\Response\AccountSchema;
use App\Shared\Infrastructure\Redis\CacheServiceInterface;
use PHPUnit\Framework\TestCase;

class GetAccountDetailsQueryHandlerTest extends TestCase
{
    public function testHandleReturnsCachedAccount(): void
    {
        $accountId = '12345';
        $cacheKey = md5('account-details:'.$accountId);
        $mockAccount = $this->createMock(Account::class);
        $mockCache = $this->createMock(CacheServiceInterface::class);
        $mockRepository = $this->createMock(AccountRepositoryInterface::class);

        $mockCache->expects($this->once())
            ->method('keyExist')
            ->with($cacheKey)
            ->willReturn(true);

        $mockCache->expects($this->once())
            ->method('getFromStore')
            ->with($cacheKey, Account::class)
            ->willReturn($mockAccount);

        $mockRepository->expects($this->never())
            ->method('findByAccountId');

        $handler = new GetAccountDetailsQueryHandler($mockRepository, $mockCache);

        $query = new GetAccountDetailsQuery($accountId);

        $result = $handler->__invoke($query);

        $this->assertInstanceOf(AccountSchema::class, $result);
    }

    public function testHandleStoresAndReturnsAccountWhenNotCached(): void
    {
        $accountId = '12345';
        $cacheKey = md5('account-details:'.$accountId);
        $mockAccount = $this->createMock(Account::class);
        $mockCache = $this->createMock(CacheServiceInterface::class);
        $mockRepository = $this->createMock(AccountRepositoryInterface::class);

        $mockCache->expects($this->once())
            ->method('keyExist')
            ->with($cacheKey)
            ->willReturn(false);

        $mockCache->expects($this->never())
            ->method('getFromStore');

        $mockRepository->expects($this->once())
            ->method('findByAccountId')
            ->with($accountId)
            ->willReturn($mockAccount);

        $mockCache->expects($this->once())
            ->method('store')
            ->with($cacheKey, $mockAccount, $mockCache->getDefaultTtl(), Account::class);

        $handler = new GetAccountDetailsQueryHandler($mockRepository, $mockCache);

        $query = new GetAccountDetailsQuery($accountId);

        $result = $handler->__invoke($query);

        $this->assertInstanceOf(AccountSchema::class, $result);
    }

    public function testHandleThrowsExceptionWhenAccountNotFound(): void
    {
        $accountId = '12345';
        $cacheKey = md5('account-details:'.$accountId);
        $mockCache = $this->createMock(CacheServiceInterface::class);
        $mockRepository = $this->createMock(AccountRepositoryInterface::class);

        $mockCache->expects($this->once())
            ->method('keyExist')
            ->with($cacheKey)
            ->willReturn(false);

        $mockCache->expects($this->never())
            ->method('getFromStore');

        $mockRepository->expects($this->once())
            ->method('findByAccountId')
            ->with($accountId)
            ->willReturn(null);

        $handler = new GetAccountDetailsQueryHandler($mockRepository, $mockCache);

        $query = new GetAccountDetailsQuery($accountId);

        $this->expectException(AccountException::class);
        $this->expectExceptionMessage('account not found');

        $handler->__invoke($query);
    }
}
