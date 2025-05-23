<?php

namespace App\Tests\Unit\Accounts\Command;

use App\Accounts\Application\Command\AddAccountCommand;
use App\Accounts\Application\Command\AddAccountCommandHandler;
use App\Accounts\Domain\Model\Account;
use App\Accounts\Infrastructure\Persistence\Doctrine\AccountRepositoryInterface;
use App\Shared\Domain\Contracts\UidGeneratorInterface;
use App\Shared\Domain\Model\Uid;
use App\Shared\Infrastructure\Redis\CacheServiceInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class AddAccountCommandHandlerTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testHandleCreatesNewAccount(): void
    {
        // Mocks
        $command = new AddAccountCommand(
            'account-id-1',
            'Test Account',
            'John',
            'Doe',
            'CIF123456',
            'Organization Name',
            'org-id-1',
            'Product Name',
            'prod-code',
            'prod-id',
            'msl-123',
            'client-456'
        );

        $mockUid = $this->createMock(Uid::class);

        $mockLogger = $this->createMock(LoggerInterface::class);

        $mockUidGenerator = $this->createMock(UidGeneratorInterface::class);
        $mockUidGenerator->expects($this->once())
            ->method('generate')
            ->willReturn($mockUid);

        $mockAccountRepository = $this->createMock(AccountRepositoryInterface::class);
        $mockAccountRepository->expects($this->once())
            ->method('findByAccountId')
            ->with($command->accountId)
            ->willReturn(null);

        $mockAccountRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Account::class));

        $mockCache = $this->createMock(CacheServiceInterface::class);
        $mockCache->expects($this->never())
            ->method('keyExist');
        $mockCache->expects($this->never())
            ->method('invalidate');

        $handler = new AddAccountCommandHandler($mockAccountRepository, $mockUidGenerator, $mockCache, $mockLogger);

        $handler->__invoke($command);
    }

    public function testHandleUpdatesExistingAccountAndInvalidatesCache(): void
    {
        $command = new AddAccountCommand(
            'account-id-1',
            'Test Account Updated',
            'John',
            'Smith',
            'CIF123456',
            'New Organization',
            'org-id-1',
            'Updated Product Name',
            'new-prod-code',
            'new-prod-id',
            'msl-123',
            'client-789'
        );

        $mockLogger = $this->createMock(LoggerInterface::class);

        $existingAccount = $this->createMock(Account::class);
        $existingAccount->expects($this->once())
            ->method('updateWithFreshData')
            ->with($this->isInstanceOf(Account::class));

        $mockUidGenerator = $this->createMock(UidGeneratorInterface::class);

        $mockAccountRepository = $this->createMock(AccountRepositoryInterface::class);
        $mockAccountRepository->expects($this->once())
            ->method('findByAccountId')
            ->with($command->accountId)
            ->willReturn($existingAccount);

        $mockAccountRepository->expects($this->once())
            ->method('save')
            ->with($existingAccount);

        $mockCache = $this->createMock(CacheServiceInterface::class);

        $mockCache->expects($this->once())
            ->method('keyExist')
            ->with('account-details-'.$command->accountId)
            ->willReturn(true);

        $mockCache->expects($this->once())
            ->method('invalidate')
            ->with('account-details-'.$command->accountId);

        $handler = new AddAccountCommandHandler($mockAccountRepository, $mockUidGenerator, $mockCache, $mockLogger);

        $handler->__invoke($command);
    }

    /**
     * @throws Exception
     */
    public function testHandleDoesNotInvalidateCacheIfKeyDoesNotExist(): void
    {
        $command = new AddAccountCommand(
            'account-id-1',
            'Test Account Updated',
            'John',
            'Smith',
            'CIF123456',
            'New Organization',
            'org-id-1',
            'Updated Product Name',
            'new-prod-code',
            'new-prod-id',
            'msl-123',
            'client-789'
        );

        $existingAccount = $this->createMock(Account::class);

        $mockLogger = $this->createMock(LoggerInterface::class);
        $existingAccount->expects($this->once())
            ->method('updateWithFreshData')
            ->with($this->isInstanceOf(Account::class));

        $mockUidGenerator = $this->createMock(UidGeneratorInterface::class);

        $mockAccountRepository = $this->createMock(AccountRepositoryInterface::class);
        $mockAccountRepository->expects($this->once())
            ->method('findByAccountId')
            ->with($command->accountId)
            ->willReturn($existingAccount);

        $mockAccountRepository->expects($this->once())
            ->method('save')
            ->with($existingAccount);

        $mockCache = $this->createMock(CacheServiceInterface::class);
        $mockCache->expects($this->once())
            ->method('keyExist')
            ->with('account-details-'.$command->accountId)
            ->willReturn(false);

        $mockCache->expects($this->never())
            ->method('invalidate');

        $handler = new AddAccountCommandHandler($mockAccountRepository, $mockUidGenerator, $mockCache, $mockLogger);

        $handler->__invoke($command);
    }
}
