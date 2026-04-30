<?php

namespace App\Tests\Integration\Task;

use App\Models\Task\Task;
use App\Models\Task\Enum\TaskStatus;
use App\Models\Task\Enum\TaskType;
use App\Tests\Integration\DatabaseTestCase;
use App\Models\Task\Contract\TaskDatabaseRepositoryInterface;

class TaskRepositoryTest extends DatabaseTestCase
{
    private TaskDatabaseRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = static::getContainer()
            ->get(TaskDatabaseRepositoryInterface::class);
    }

    public function testSaveAndFind(): void
    {
        $task = new Task(
            videoId: 1,
            type: TaskType::transcoding,
            inputData: ['file' => 'test.mp4'],
            source: 'test'
        );

        $this->repository->save($task);
        $this->em->flush();

        $found = $this->repository->find($task->getId());

        $this->assertNotNull($found);
        $this->assertEquals(1, $found->getVideoId());
        $this->assertEquals(TaskStatus::pending, $found->getStatus());
    }

    public function testFindRetryableTasks(): void
    {
        $task = new Task(
            videoId: 1,
            type: TaskType::transcoding,
            inputData: [],
            source: 'test'
        );
        $task->markRunning();
        $task->markPending();
        $task->setNextRetryAt(new \DateTimeImmutable('-1 minute'));

        $this->repository->save($task);
        $this->em->flush();

        $result = $this->repository->findRetryableTasks(10);

        $this->assertCount(1, $result);
    }
}
