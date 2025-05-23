<?php

namespace App\Authentication\Infrastructure\Persistence;

use App\AccessToken\Domain\Model\AccessToken;
use App\AccessToken\Infrastructure\Persistence\Doctrine\AccessTokenRepositoryInterface as DoctrineAccessTokenRepository;

readonly class InEnvAccessTokenRepository
{
    public function __construct(
        private DoctrineAccessTokenRepository $accessTokenRepository
    ) {
    }

    public function findOneByValue(string $accessToken): ?AccessToken
    {
        if ($this->accessTokenRepository->findById($accessToken)) {
            return $this->accessTokenRepository->findById($accessToken);
        }

        return null;
    }
}
