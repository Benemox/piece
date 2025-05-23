<?php

namespace App\AccessToken\Application\Command\CreateAccessToken;

use App\AccessToken\Domain\Model\AccessToken;
use App\AccessToken\Infrastructure\Persistence\Doctrine\AccessTokenRepositoryInterface;
use App\Shared\Domain\Bus\HandlerInterface;

readonly class CreateAccessTokenCommandHandler implements HandlerInterface
{
    public function __construct(
        private AccessTokenRepositoryInterface $accessTokenRepository,
    ) {
    }

    public function __invoke(CreateAccessTokenCommand $command): AccessToken
    {
        $accessToken = AccessToken::create(
            $command->role
        );
        $this->accessTokenRepository->save($accessToken);

        return $accessToken;
    }
}
