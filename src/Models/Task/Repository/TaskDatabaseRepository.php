<?php

namespace App\Models\Task\Repository;

use App\Models\Common\DTO\PaginatedResultDTO;
use App\Models\Task\Collection\TaskCollection;
use App\Models\Task\Contract\TaskDatabaseRepositoryInterface;
use App\Models\Task\Enum\TaskStatus;
use App\Models\Task\Enum\TaskType;
use App\Models\Task\Filter\TaskFilter;
use App\Models\Task\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TaskDatabaseRepository extends ServiceEntityRepository implements TaskDatabaseRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function save(Task $task, bool $flush = false): void
    {
        $em = $this->getEntityManager();
        $em->persist($task);

        if ($flush) {
            $em->flush();
        }
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
        $conn = $this->getEntityManager()->getConnection();
        $sql = <<<SQL
            select id
            from tasks
            where status = :status
              and next_retry_at <= now()
            order by next_retry_at asc
            limit :limit
            for update skip locked
        SQL;
        $ids = $conn->fetchFirstColumn(
            $sql,
            [
                'status' => TaskStatus::pending->value,
                'limit' => $limit,
            ]
        );

        if (empty($ids)) {
            return [];
        }

        // 🔥 2. загружаем сущности через Doctrine
        return $this->createQueryBuilder('t')
            ->where('t.id IN (:ids)')
            ->setParameter('ids', $ids)
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

    /**
     * @param TaskFilter $filter
     * @return PaginatedResultDTO<TaskCollection>
     */
    public function findPaginated(TaskFilter $filter): PaginatedResultDTO
    {
        $qb = $this->createQueryBuilder('t');

        if ($filter->status !== null) {
            $qb->andWhere('t.status = :status')
                ->setParameter('status', $filter->status);
        }

        if ($filter->type !== null) {
            $qb->andWhere('t.type = :type')
                ->setParameter('type', $filter->type);
        }

        $countQb = (clone $qb);

        $items = $qb
            ->orderBy('t.' . $filter->sortBy, $filter->direction)
            ->setFirstResult($filter->offset)
            ->setMaxResults($filter->limit)
            ->getQuery()
            ->getResult();

        $total = (int) $countQb
            ->select('count(t.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return new PaginatedResultDTO(
            new TaskCollection($items),
            $total
        );
    }
}
