<?php

namespace App\Tests\Unit\Accounts\Query;

use App\Accounts\Application\Query\GetAccountInCsvQuery;
use App\Accounts\Application\Query\GetAccountInCsvQueryHandler;
use App\Accounts\Domain\Model\Account;
use App\Accounts\Infrastructure\Persistence\Doctrine\AccountRepositoryInterface;
use App\Shared\Infrastructure\File\Csv\CsvFileHandlerInterface;
use App\Shared\Infrastructure\Redis\CacheServiceInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class GetAccountInCsvQueryHandlerTest extends TestCase
{
    public function testHandleReturnsCachedCsv(): void
    {
        $updateDate = new \DateTimeImmutable('2024-12-23');
        $cacheKey = md5('update-accounts-'.$updateDate->format('Y-m-d'));
        $cachedCsv = 'accountId,accountName,memberName,mslCustomerId';

        $mockCache = $this->createMock(CacheServiceInterface::class);
        $mockRepository = $this->createMock(AccountRepositoryInterface::class);
        $mockCsvHandler = $this->createMock(CsvFileHandlerInterface::class);
        $mockLogger = $this->createMock(LoggerInterface::class);

        $mockCache->expects($this->once())
            ->method('keyExist')
            ->with($cacheKey)
            ->willReturn(true);

        $mockCache->expects($this->once())
            ->method('getFromStore')
            ->with($cacheKey, 'string')
            ->willReturn($cachedCsv);

        $mockRepository->expects($this->never())
            ->method('findAfterDate');

        $mockCsvHandler->expects($this->never())
            ->method('buildCsv');

        $mockLogger->expects($this->never())
            ->method('info');

        $handler = new GetAccountInCsvQueryHandler($mockRepository, $mockCsvHandler, $mockLogger, $mockCache);

        $query = new GetAccountInCsvQuery($updateDate);

        $result = $handler->__invoke($query);

        $this->assertEquals($cachedCsv, $result);
    }

    public function testHandleGeneratesCsvAndStoresInCache(): void
    {
        $updateDate = new \DateTimeImmutable('2024-12-23');
        $cacheKey = md5('update-accounts-'.$updateDate->format('Y-m-d'));
        $mockAccount = $this->createMock(Account::class);
        $mockAccounts = [$mockAccount];
        $generatedCsv = 'accountId,accountName,memberName,mslCustomerId';

        $mockCache = $this->createMock(CacheServiceInterface::class);
        $mockRepository = $this->createMock(AccountRepositoryInterface::class);
        $mockCsvHandler = $this->createMock(CsvFileHandlerInterface::class);
        $mockLogger = $this->createMock(LoggerInterface::class);

        $mockCache->expects($this->once())
            ->method('keyExist')
            ->with($cacheKey)
            ->willReturn(false);

        $mockCache->expects($this->once())
            ->method('store')
            ->with($cacheKey, $generatedCsv, $mockCache->getDefaultTtl(), 'string');

        $mockRepository->expects($this->once())
            ->method('findAfterDate')
            ->with($updateDate)
            ->willReturn($mockAccounts);

        $mockCsvHandler->expects($this->once())
            ->method('buildCsv')
            ->with($this->isType('array'), $this->isType('array'))
            ->willReturn($generatedCsv);

        $mockLogger->expects($this->once())
            ->method('info')
            ->with('Accounts update data requested and exported to csv', $this->isType('array'));

        $handler = new GetAccountInCsvQueryHandler($mockRepository, $mockCsvHandler, $mockLogger, $mockCache);

        $query = new GetAccountInCsvQuery($updateDate);

        $result = $handler->__invoke($query);

        $this->assertEquals($generatedCsv, $result);
    }
}
