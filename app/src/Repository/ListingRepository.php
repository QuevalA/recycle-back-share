<?php

namespace App\Repository;

use App\Entity\Listing;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Listing>
 *
 * @method Listing|null find($id, $lockMode = null, $lockVersion = null)
 * @method Listing|null findOneBy(array $criteria, array $orderBy = null)
 * @method Listing[]    findAll()
 * @method Listing[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Listing::class);
    }

    public function save(Listing $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Listing $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function search($latitude,$longitude,$round,$fkListingType)
    {
            return $this->createQueryBuilder('l')
            ->andWhere('l.latitude > :min_lat')
            ->andWhere('l.latitude < :max_lat')
            ->andWhere('l.longitude > :min_lng')
            ->andWhere('l.longitude < :max_lng')  
            ->andWhere('l.fkListingType = :type')
            ->andWhere('l.fkListingStatus = 1')
            ->setParameter('min_lat', $latitude - ($round/111))
            ->setParameter('max_lat', $latitude + ($round/111))
            ->setParameter('min_lng', $longitude - ($round/76))
            ->setParameter('max_lng', $longitude + ($round/76))
            ->setParameter('type', $fkListingType)
            ->getQuery()->getResult();
    }

    public function findByCategory($categoryId)
    {
        return $this->createQueryBuilder('l')
            ->join('l.subCategory', 'sc')
            ->join('sc.fkListingCategory', 'lc')
            ->where('lc.id = :categoryId')
            ->andWhere('l.fkListingStatus = :statusId')
            ->setParameter('categoryId', $categoryId)
            ->setParameter('statusId', 1)
            ->getQuery()
            ->getResult();
    }

    public function batchUpdateListingStatus($listingIds, $statusId)
    {
        $qb = $this->createQueryBuilder('l');
        $qb->update(Listing::class, 'l')
            ->set('l.fkListingStatus', ':status')
            ->where($qb->expr()->in('l.id', ':listingIds'))
            ->setParameter('status', $statusId)
            ->setParameter('listingIds', $listingIds)
            ->getQuery()
            ->execute();

        $updatedListingStatuses = $this->createQueryBuilder('l')
            ->select('l.id, ls.status')
            ->join('l.fkListingStatus', 'ls')
            ->where('l.id IN (:listingIds)')
            ->setParameter('listingIds', $listingIds)
            ->getQuery()
            ->getArrayResult();

        $updatedListingStatus = [];
        foreach ($updatedListingStatuses as $status) {
            $updatedListingStatus[$status['id']] = $status['status'];
        }

        return $updatedListingStatus;
    }
}
