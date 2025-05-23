<?php

namespace App\AccessToken\Application\Command\UpdateAccessToken;

use App\AccessToken\Domain\Exception\AccessTokenException;
use App\AccessToken\Infrastructure\Persistence\Doctrine\AccessTokenRepositoryInterface;
use App\Shared\Domain\Bus\HandlerInterface;

readonly class UpdateAccessTokenRoleCommandHandler implements HandlerInterface
{
    public function __construct(
        private readonly AccessTokenRepositoryInterface $accessTokenRepository
    ) {
    }

    public function __invoke(UpdateAccessTokenRoleCommand $command): void
    {
        $accessToken = $this->accessTokenRepository->findById($command->token);

        if (!$accessToken) {
            throw AccessTokenException::invalidId();
        }

        $accessToken->setRole($command->role);
        $this->accessTokenRepository->save($accessToken);
    }
}
