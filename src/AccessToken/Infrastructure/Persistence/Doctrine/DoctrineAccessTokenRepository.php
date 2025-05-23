<?php

namespace App\AccessToken\Infrastructure\Persistence\Doctrine;

use App\AccessToken\Application\Query\ListAccessTokenFilters;
use App\AccessToken\Domain\Model\AccessToken;
use App\Shared\Http\Pagination\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AccessToken>
 */
class DoctrineAccessTokenRepository extends ServiceEntityRepository implements AccessTokenRepositoryInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        ManagerRegistry $registry,
        EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, AccessToken::class);
        $this->entityManager = $entityManager;
    }

    /**
     * @return AccessToken[]
     */
    public function findAll(): array
    {
        $qb = $this->createQueryBuilder('u')->select('u');

        return $qb->getQuery()->execute();
    }

    public function save(AccessToken $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findById(string $token): ?AccessToken
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.token = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return AccessToken[]
     */
    public function paginatedFilters(ListAccessTokenFilters $filters, Page $page): array
    {
        $qb = $this->createQueryBuilder('u');
        $this->applyFilters($qb, $filters);

        $qb->setFirstResult($page->offset());
        $qb->setMaxResults($page->limit());

        return $qb->getQuery()->execute();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function countByFilters(ListAccessTokenFilters $filters): int
    {
        $qb = $this->createQueryBuilder('u');
        $qb->select('count(u.token)');

        $this->applyFilters($qb, $filters);

        return $qb->getQuery()->getSingleScalarResult(); // @phpstan-ignore-line
    }

    public function remove(AccessToken $user): void
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    private function applyFilters(QueryBuilder $qb, ListAccessTokenFilters $filters): void
    {
        if ($userRole = $filters->role()) {
            $qb->andWhere('u.role = :role')
                ->setParameter('role', $userRole->getValue());
        }
    }
}
