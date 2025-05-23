<?php

namespace App\Authentication\Application;

use App\AccessToken\Infrastructure\Persistence\Doctrine\AccessTokenRepositoryInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

readonly class AccessTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(
        private AccessTokenRepositoryInterface $repository
    ) {
    }

    public function getUserBadgeFrom(#[\SensitiveParameter] string $accessToken): UserBadge
    {
        $user = $this->repository->findById($accessToken);

        if (null === $user) {
            throw new BadCredentialsException('Invalid credentials.');
        }

        return new UserBadge($user->getToken());
    }
}
