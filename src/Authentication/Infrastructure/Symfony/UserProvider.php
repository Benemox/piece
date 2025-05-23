<?php

namespace App\Authentication\Infrastructure\Symfony;

use App\AccessToken\Domain\Model\AccessToken;
use App\AccessToken\Infrastructure\Persistence\Doctrine\AccessTokenRepositoryInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    private AccessTokenRepositoryInterface $accessTokenRepository;

    public function __construct(AccessTokenRepositoryInterface $accessTokenRepository)
    {
        $this->accessTokenRepository = $accessTokenRepository;
    }

    public function loadUserByIdentifier(string $accessToken): UserInterface
    {
        $tokenEntity = $this->accessTokenRepository->findById($accessToken);

        if (!$tokenEntity) {
            throw new UserNotFoundException(sprintf('Access Token "%s" no encontrado.', $accessToken));
        }

        return $tokenEntity;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof AccessToken) {
            throw new \InvalidArgumentException('El usuario no es una instancia de AccessToken.');
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return AccessToken::class === $class;
    }
}
