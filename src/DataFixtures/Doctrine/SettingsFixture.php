<?php

namespace App\DataFixtures\Doctrine;

use App\Settings\Domain\Model\AvailableSettings;
use App\Settings\Domain\Model\Setting;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'local')]
#[When(env: 'test')]
class SettingsFixture extends Fixture implements OrderedFixtureInterface
{
    public const SETTINGS = [
        [
            '94c680da-a393-41f6-97cd-e18eae12d467',
            AvailableSettings::TRANSACTION_ACCOUNT_ENRICHER_FORWARDING,
            'true',
        ],
        [
            'cd737ecb-15a2-44e0-a539-37fb66c3ae4f',
            AvailableSettings::TRANSACTION_COMMERCE_ENRICHER_FORWARDING,
            'true',
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::SETTINGS as $data) {
            $setting = new Setting(
                $data[0],
                $data[1],
                $data[2]
            );
            $manager->persist($setting);
        }
        $manager->flush();
    }

    public function getOrder(): int
    {
        return 3;
    }
}
