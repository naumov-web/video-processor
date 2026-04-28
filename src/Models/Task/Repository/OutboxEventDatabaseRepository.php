<?php

namespace App\Models\Task\Repository;

use App\Models\Task\Contract\OutboxEventDatabaseRepositoryInterface;
use App\Models\Task\OutboxEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class OutboxEventDatabaseRepository extends ServiceEntityRepository implements OutboxEventDatabaseRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OutboxEvent::class);
    }

    public function save(OutboxEvent $event): void
    {
        $em = $this->getEntityManager();
        $em->persist($event);
        $em->flush();
    }

    /**
     * @inheritDoc
     */
    public function findPending(int $limit = 100): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.status = :status')
            ->setParameter('status', OutboxEvent::STATUS_PENDING)
            ->orderBy('e.createdAt', 'asc')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
