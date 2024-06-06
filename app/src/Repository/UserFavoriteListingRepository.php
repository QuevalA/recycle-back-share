<?php

namespace App\Repository;

use App\Entity\UserFavoriteListing;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserFavoriteListing>
 *
 * @method UserFavoriteListing|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserFavoriteListing|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserFavoriteListing[]    findAll()
 * @method UserFavoriteListing[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserFavoriteListingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserFavoriteListing::class);
    }

    public function findByUserAndListingStatus(int $userId, int $listingStatus): array
    {
        $queryBuilder = $this->createQueryBuilder('ufl')
            ->join('ufl.fkListing', 'l')
            ->andWhere('l.fkListingStatus = :status')
            ->setParameter('status', $listingStatus)
            ->andWhere('ufl.fkUser = :userId')
            ->setParameter('userId', $userId);

        return $queryBuilder->getQuery()->getResult();
    }

    public function save(UserFavoriteListing $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UserFavoriteListing $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
