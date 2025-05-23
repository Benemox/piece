<?php

namespace App\Tests\Unit\AccessToken;

use App\AccessToken\Application\Command\CreateAccessToken\CreateAccessTokenCommand;
use App\AccessToken\Application\Command\CreateAccessToken\CreateAccessTokenCommandHandler;
use App\AccessToken\Application\Command\RemoveAccessToken\RemoveAccessTokenCommand;
use App\AccessToken\Application\Command\RemoveAccessToken\RemoveAccessTokenCommandHandler;
use App\AccessToken\Domain\Model\AccessToken;
use App\AccessToken\Domain\Model\Role;
use App\AccessToken\Infrastructure\Persistence\Doctrine\AccessTokenRepositoryInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class TokenControllerTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testCreateTokenSuccess(): void
    {
        $command = new CreateAccessTokenCommand(Role::roleAdmin());

        $mockAccessTokenRepository = $this->createMock(AccessTokenRepositoryInterface::class);
        $mockAccessTokenRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(AccessToken::class));

        $handler = new CreateAccessTokenCommandHandler($mockAccessTokenRepository);

        $handler->__invoke($command);
    }

    public function testListAccessToken(): void
    {
        $mockAccessTokenRepository = $this->createMock(AccessTokenRepositoryInterface::class);
        $mockAccessTokenRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([
                AccessToken::createWithToken('mocked-token-1', Role::roleAdmin()),
                AccessToken::createWithToken('mocked-token-2', Role::roleConsultant()),
            ]);

        $accessTokens = $mockAccessTokenRepository->findAll();

        $this->assertCount(2, $accessTokens);
        $this->assertEquals('mocked-token-1', $accessTokens[0]->getToken());
    }

    public function testRemoveAccessTokenSuccess(): void
    {
        $mockAccessToken = $this->createMock(AccessToken::class);
        $mockAccessToken->method('getToken')->willReturn('mocked-token');

        $mockAccessTokenRepository = $this->createMock(AccessTokenRepositoryInterface::class);
        $mockAccessTokenRepository->expects($this->once())
            ->method('findById')
            ->with('mocked-token')
            ->willReturn($mockAccessToken);

        $mockAccessTokenRepository->expects($this->once())
            ->method('remove')
            ->with($this->isInstanceOf(AccessToken::class));

        $mockLogger = $this->createMock(LoggerInterface::class);

        $command = new RemoveAccessTokenCommand('mocked-token');
        $handler = new RemoveAccessTokenCommandHandler($mockAccessTokenRepository, $mockLogger);

        $handler->__invoke($command);
    }
}
