<?php

namespace App\Tests\Behat;

use App\AccessToken\Infrastructure\Persistence\Doctrine\AccessTokenRepositoryInterface;
use Behatch\Context\BaseContext;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class SecurityContext extends BaseContext
{
    public function __construct(
        private AccessTokenRepositoryInterface $accessTokenRepository,
    ) {
    }

    /**
     * @Given I am authenticated with token :token
     *
     * @throws \Exception
     */
    public function iAmAuthenticated(string $token): void
    {
        $user = $this->accessTokenRepository->findById($token);

        if (null === $user) {
            throw new \Exception('AccessToken not found');
        }

        /** @var KernelBrowser $client */
        $client = $this->getSession()->getDriver()->getClient(); // @phpstan-ignore-line

        $client->setServerParameter('HTTP_AUTHORIZATION', 'Bearer '.$token);
    }

    /**
     * @Given As anonymous user
     */
    public function asAnonymousUser(): void
    {
        /** @var KernelBrowser $client */
        $client = $this->getSession()->getDriver()->getClient(); // @phpstan-ignore-line
        $client->getCookieJar()->clear();
    }
}
