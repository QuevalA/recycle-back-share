<?php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 *
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function save(Message $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Message $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findLatestMessageByConversationId(int $conversationId): ?Message
    {
        return $this->findOneBy(
            ['fkConversation' => $conversationId],
            ['createdAt' => 'DESC']
        );
    }

    public function getConversationIdForUserAndListing(int $userId, int $listingId): ?int
    {
        $qb = $this->createQueryBuilder('m');
        $qb->select('c.id')
            ->innerJoin('m.fkConversation', 'c')
            ->where($qb->expr()->eq('m.fkUserSender', ':userId'))
            ->setParameter('userId', $userId)
            ->andWhere($qb->expr()->eq('c.fkListing', ':listingId'))
            ->setParameter('listingId', $listingId);

        $result = $qb->getQuery()->getOneOrNullResult();

        if ($result) {
            return $result['id'];
        }

        return null;
    }
}
