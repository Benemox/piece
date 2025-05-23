<?php

namespace App\AccessToken\Application\Command\RemoveAccessToken;

use App\AccessToken\Domain\Exception\AccessTokenException;
use App\AccessToken\Infrastructure\Persistence\Doctrine\AccessTokenRepositoryInterface;
use App\Shared\Domain\Bus\HandlerInterface;

readonly class RemoveAccessTokenCommandHandler implements HandlerInterface
{
    public function __construct(
        private AccessTokenRepositoryInterface $accessTokenRepository
    ) {
    }

    public function __invoke(RemoveAccessTokenCommand $command): void
    {
        if (($accessToken = $this->accessTokenRepository->findById($command->token)) === null) {
            throw AccessTokenException::invalidId();
        }

        $this->accessTokenRepository->remove($accessToken);
    }
}
