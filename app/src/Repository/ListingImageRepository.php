<?php

namespace App\Repository;

use App\Entity\ListingImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ListingImage>
 *
 * @method ListingImage|null find($id, $lockMode = null, $lockVersion = null)
 * @method ListingImage|null findOneBy(array $criteria, array $orderBy = null)
 * @method ListingImage[]    findAll()
 * @method ListingImage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListingImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ListingImage::class);
    }

    /**
     * List of all the listings
     */
    public function getAllListings()
    {

        $qb = $this->createQueryBuilder('listingImage')
            ->select('listingImage.id, listingImage.image');

        $qb->innerJoin('listingImage.fkListing', 'listing')
            ->addSelect('listing.id, listing.title, listing.description, listing.postcode, listing.city, listing.country');

//        $qb->groupBy('listingImage.fkListing');


        $query = $qb->getQuery()->getResult();

        return $query;


    }

    public function remove(ListingImage $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
