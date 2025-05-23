<?php

namespace App\Tests\Unit\Transactions\Enricher;

use App\Accounts\Domain\Model\Account;
use App\Accounts\Infrastructure\Persistence\Doctrine\AccountRepositoryInterface;
use App\Settings\Application\Services\CheckSettingServiceInterface;
use App\Shared\Infrastructure\Redis\CacheServiceInterface;
use App\Transactions\Application\Command\Enricher\AccountTransactionEnricher;
use App\Transactions\Domain\Model\MslTransaction;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class AccountTransactionEnricherTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testEnrichWithAccountDetails(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $check = $this->createMock(CheckSettingServiceInterface::class);
        $cache = $this->createMock(CacheServiceInterface::class);
        $repository = $this->createMock(AccountRepositoryInterface::class);

        $check->method('isTransactionsAccountEnrichmentEnabled')->willReturn(true);

        $cache->method('keyExist')->with('account-details-account123')->willReturn(false);
        $cache->method('getFromStore')->willReturn(null);

        $account = $this->createMock(Account::class);
        $account->method('getCif')->willReturn('B12345678');
        $account->method('getClientCode')->willReturn('12345');
        $account->method('getMemberName')->willReturn('John');
        $account->method('getMemberSurname')->willReturn('Doe');
        $account->method('getMslCustomerId')->willReturn('MSL123');
        $account->method('getOrganizationName')->willReturn('Example Org');
        $account->method('getOrganizationId')->willReturn('ORG123');
        $account->method('getProductCode')->willReturn('P123');
        $account->method('getProductName')->willReturn('Product Name');
        $account->method('getProductId')->willReturn('PID123');

        $repository->method('findByAccountId')->with('account123')->willReturn($account);

        $transaction = $this->getMockBuilder(MslTransaction::class)
            ->onlyMethods(['getAccountId', 'enrichWithAccountDetails'])
            ->disableOriginalConstructor()
            ->getMock();

        $transaction->method('getAccountId')->willReturn('account123');

        $transaction->expects($this->once())
            ->method('enrichWithAccountDetails')
            ->with(
                'B12345678',      // cif
                '12345',          // clientCode
                'John',           // memberName
                'Doe',            // memberSurname
                'MSL123',         // mslCustomerId
                'Example Org',    // organizationName
                'ORG123',         // organizationId
                'P123',           // productCode
                'Product Name',   // productName
                'PID123'          // productId
            )->willReturnSelf();

        $enricher = new AccountTransactionEnricher($repository, $cache, $logger, $check);

        $result = $enricher->enrich($transaction);

        $this->assertSame($transaction, $result);
    }
}
