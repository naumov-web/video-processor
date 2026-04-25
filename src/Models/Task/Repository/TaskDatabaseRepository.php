<?php

namespace App\Models\Task\Repository;

use App\Models\Task\Contract\TaskDatabaseRepositoryInterface;
use App\Models\Task\Enum\TaskStatus;
use App\Models\Task\Enum\TaskType;
use App\Models\Task\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TaskDatabaseRepository extends ServiceEntityRepository implements TaskDatabaseRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function save(Task $task): void
    {
        $em = $this->getEntityManager();
        $em->persist($task);
        $em->flush();
    }

    public function findById(int $id): ?Task
    {
        /** @var Task|null $task */
        $task = $this->find($id);

        return $task;
    }

    public function findNextForProcessing(): ?Task
    {
        $em = $this->getEntityManager();
        $em->beginTransaction();

        try {
            $conn = $em->getConnection();
            $sql = <<<SQL
                select id
                from tasks
                where status = :status
                order by priority desc, created_at asc
                limit 1
                for update skip locked
            SQL;
            $id = $conn->fetchOne($sql, [
                'status' => TaskStatus::pending->value,
            ]);

            if (!$id) {
                $em->commit();
                return null;
            }

            /** @var Task $task */
            $task = $this->find($id);
            $task->markRunning();
            $em->flush();
            $em->commit();

            return $task;
        } catch (\Throwable $e) {
            $em->rollback();
            throw $e;
        }
    }

    public function findRetryableTasks(int $limit = 10): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.status = :status')
            ->andWhere('t.nextRetryAt <= :now')
            ->setParameter('status', TaskStatus::pending)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('t.nextRetryAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function existsActiveTaskForVideo(int $videoId, TaskType $type): bool
    {
        $qb = $this->createQueryBuilder('t');

        $count = $qb
            ->select('COUNT(t.id)')
            ->where('t.videoId = :videoId')
            ->andWhere('t.type = :type')
            ->andWhere('t.status IN (:statuses)')
            ->setParameter('videoId', $videoId)
            ->setParameter('type', $type)
            ->setParameter('statuses', [
                TaskStatus::pending,
                TaskStatus::running,
            ])
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }
}
