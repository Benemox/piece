<?php

namespace App\Settings\Infrastructure\Persistence\Doctrine;

use App\Settings\Domain\Model\Setting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Setting>
 */
class DoctrineSettingsRepository extends ServiceEntityRepository implements SettingsRepositoryInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        ManagerRegistry $registry,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct($registry, Setting::class);
        $this->entityManager = $entityManager;
    }

    public function save(Setting $provider): void
    {
        $this->entityManager->persist($provider);
        $this->entityManager->flush();
    }

    public function findAllSettings(): array
    {
        $qb = $this->createQueryBuilder('s')->select('s');

        return $qb->getQuery()->getResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findSetting(string $settingName): ?Setting
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.settingName = :name')
            ->setParameter('name', $settingName)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
