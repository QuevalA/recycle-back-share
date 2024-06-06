<?php

namespace App\Repository;

use App\Entity\ListingTypeSubType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ListingTypeSubType>
 *
 * @method ListingTypeSubType|null find($id, $lockMode = null, $lockVersion = null)
 * @method ListingTypeSubType|null findOneBy(array $criteria, array $orderBy = null)
 * @method ListingTypeSubType[]    findAll()
 * @method ListingTypeSubType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListingTypeSubTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ListingTypeSubType::class);
    }

    public function save(ListingTypeSubType $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ListingTypeSubType $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return ListingTypeSubType[] Returns an array of ListingTypeSubType objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ListingTypeSubType
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
