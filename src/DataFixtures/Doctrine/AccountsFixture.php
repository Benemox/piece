<?php

namespace App\DataFixtures\Doctrine;

use App\Accounts\Domain\Model\Account;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'local')]
#[When(env: 'test')]
class AccountsFixture extends Fixture implements OrderedFixtureInterface
{
    public const ACCOUNTS = [
        [
            Uuids::ACCOUNT_UID_1,              // id
            '843578020000131667',              // accountId
            '001-Comida',                     // accountName
            'Jonh',                           // memberName
            'Doe',                            // memberSurname
            '12345678A',                      // cif
            '943578020000372508',             // mslCustomerId (anteriormente estaba en la posición 13)
            '2024-11-25T12:45:26+01:00',        // updateDate (anteriormente estaba en la posición 12)
            'INFORMA DyB S.A.U',              // organizationName (posición 6 en el array original)
            Uuids::ORGANIZATION_UID_1,         // organizationId (posición 7)
            '2889',                           // clientCode (posición 8)
            'Beneficio Social 01',            // productName (posición 9)
            'UP01INFORMADYBSAU001',            // productCode (posición 10)
            Uuids::PRODUCT_UID_1,              // productId (posición 11)
        ],
        [
            Uuids::ACCOUNT_UID_2,              // id
            '843578020000131668',              // accountId
            '002-Transporte',                 // accountName
            'Xavier',                         // memberName
            'Bonilla Sanchez',                // memberSurname
            '12345678A',                      // cif
            '943578020000372510',             // mslCustomerId
            '2024-11-25T12:45:26+01:00',        // updateDate
            'INFORMA DyB S.A.U',              // organizationName
            Uuids::ORGANIZATION_UID_1,         // organizationId
            '2889',                           // clientCode
            'Beneficio Social 02',            // productName
            'UP02INFORMADYBSAU002',            // productCode
            Uuids::PRODUCT_UID_2,              // productId
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::ACCOUNTS as $data) {
            $account = new Account(
                $data[0],
                $data[1],
                $data[2],
                $data[3],
                $data[4],
                $data[5],
                $data[6],
                $data[7],
                $data[8],
                $data[9],
                $data[10],
                $data[11],
                $data[12],
                $data[13]
            );
            $manager->persist($account);
        }
        $manager->flush();
    }

    public function getOrder(): int
    {
        return 1;
    }
}
