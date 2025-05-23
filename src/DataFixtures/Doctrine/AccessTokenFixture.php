<?php

namespace App\DataFixtures\Doctrine;

use App\AccessToken\Domain\Model\AccessToken;
use App\AccessToken\Domain\Model\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'local')]
#[When(env: 'test')]
class AccessTokenFixture extends Fixture implements OrderedFixtureInterface
{
    public const DATA = [
        [
            'token1',
            Role::ROLE_ADMIN,
        ],
        [
            'token2',
            Role::ROLE_CONSULTANT,
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::DATA as $data) {
            $access = AccessToken::createWithToken($data[0], Role::cast($data[1]));
            $manager->persist($access);
        }

        $manager->flush();
    }

    public function getOrder(): int
    {
        return 1;
    }
}
