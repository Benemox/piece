<?php

namespace App\Accounts\Infrastructure\Persistence\Doctrine;

use App\Accounts\Application\Query\ListAccountsQueryFilters;
use App\Accounts\Domain\Model\Account;
use App\Accounts\Domain\Model\Organization;
use App\Shared\Http\Pagination\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Account>
 */
class DoctrineAccountRepository extends ServiceEntityRepository implements AccountRepositoryInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        ManagerRegistry $registry,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct($registry, Account::class);
        $this->entityManager = $entityManager;
    }

    /**
     * @return Account[]
     */
    public function findAll(): array
    {
        $qb = $this->createQueryBuilder('a')->select('a');

        return $qb->getQuery()->execute();
    }

    public function save(Account $account): void
    {
        $this->entityManager->persist($account);
        $this->entityManager->flush();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findByAccountId(string $accountId): ?Account
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.accountId = :id')
            ->setParameter('id', $accountId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAfterDate(\DateTimeImmutable $updateDate): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.updateDate >= :updateDate')
            ->setParameter('updateDate', $updateDate, 'date')
            ->getQuery()
            ->execute();
    }

    /**
     * @return Organization[]
     */
    public function getAccountsOrganizations(): array
    {
        $organizations = $this->createQueryBuilder('a')
            ->select('a.organizationName, a.organizationId, a.cif')
            ->groupBy('a.organizationName, a.organizationId, a.cif')
            ->getQuery()
            ->getResult();

        return array_map(static function ($organization) {
            return new Organization(
                name: $organization['organizationName'],
                cif: $organization['cif'],
                organizationId: $organization['organizationId']
            );
        }, $organizations);
    }

    public function listAndFilter(ListAccountsQueryFilters $filters, Page $page): array
    {
        $qb = $this->createQueryBuilder('a');

        $this->applyFilters($qb, $filters);

        $qb->setFirstResult($page->offset());
        $qb->setMaxResults($page->limit());

        return $qb->getQuery()->getResult();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function countByFilters(ListAccountsQueryFilters $filters, Page $page): int
    {
        $qb = $this->createQueryBuilder('a')
            ->select('count(a.id )');

        $this->applyFilters($qb, $filters);

        $qb->setFirstResult($page->offset());
        $qb->setMaxResults($page->limit());

        return $qb->getQuery()->getSingleScalarResult(); // @phpstan-ignore-line
    }

    public function applyFilters(QueryBuilder $qb, ListAccountsQueryFilters $filters): void
    {
        if (null !== $filters->getOrganizationId()) {
            $qb->andWhere('a.organizationId = :organizationId')
                ->setParameter('organizationId', $filters->getOrganizationId()->value());
        }

        if (null !== $filters->getCif()) {
            $qb->andWhere('a.cif = :cif')
                ->setParameter('cif', $filters->getCif());
        }
    }
}
