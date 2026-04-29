<?php

namespace App\Models\Task\Repository;

use App\Models\Common\DTO\PaginatedResultDTO;
use App\Models\Task\Collection\TaskCollection;
use App\Models\Task\Contract\TaskDatabaseRepositoryInterface;
use App\Models\Task\DTO\VideoStatisticDTO;
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

    public function getById(int $id): ?Task
    {
        /** @var Task|null $model */
        $model = $this->find($id);

        return $model;
    }

    public function updateHeartbeat(int $taskId): void
    {
        $this->getEntityManager()->createQueryBuilder()
            ->update(Task::class, 't')
            ->set('t.lastHeartbeatAt', ':now')
            ->where('t.id = :id')
            ->setParameter('now', new \DateTimeImmutable())
            ->setParameter('id', $taskId)
            ->getQuery()
            ->execute();
    }

    public function findStaleRunningTasks(\DateTimeImmutable $threshold, int $limit): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.status = :status')
            ->andWhere('t.lastHeartbeatAt IS NOT NULL')
            ->andWhere('t.lastHeartbeatAt < :threshold')
            ->setParameter('status', TaskStatus::running->value)
            ->setParameter('threshold', $threshold)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getVideoStatistic(int $videoId): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = <<<SQL
            select
                id,
                status,
                type,
                created_at,

                row_number() over (
                    partition by video_id
                    order by created_at, id
                ) as attempt_number,

                lag(created_at) over (
                    partition by video_id
                    order by created_at, id
                ) as prev_attempt_at,

                TO_CHAR(
                    created_at - LAG(created_at) OVER (
                        PARTITION BY video_id
                        ORDER BY created_at, id
                    ),
                    'HH24:MI:SS'
                ) as retry_delay,

                COUNT(*) OVER (PARTITION BY video_id) as total_tasks,

                COUNT(*) FILTER (WHERE status = 'completed')
                    OVER (PARTITION BY video_id) as success_count,

                COUNT(*) FILTER (WHERE status = 'failed')
                    OVER (PARTITION BY video_id) as failed_count

            from tasks
            where video_id = :videoId
            order by created_at, id;
        SQL;
        $rows = $conn->executeQuery($sql, [
            'videoId' => $videoId
        ])->fetchAllAssociative();

        return array_map(function (array $row) {
            return new VideoStatisticDTO(
                taskId: (int) $row['id'],
                status: $row['status'],
                type: $row['type'],
                createdAt: new \DateTimeImmutable($row['created_at']),
                attemptNumber: (int) $row['attempt_number'],
                prevAttemptAt: $row['prev_attempt_at']
                    ? new \DateTimeImmutable($row['prev_attempt_at'])
                    : null,
                retryDelay: $row['retry_delay'] ?? null,
                totalTasks: (int) $row['total_tasks'],
                successCount: (int) $row['success_count'],
                failedCount: (int) $row['failed_count'],
            );
        }, $rows);
    }
}
