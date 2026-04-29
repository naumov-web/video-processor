<?php

namespace App\Tests\Unit\UseCase\Task;

use App\UseCase\Task\RetrySingleTaskUseCase;
use App\Infrastructure\Contract\DeduplicationServiceInterface;
use App\Models\Task\Contract\OutboxEventDatabaseRepositoryInterface;
use App\Models\Task\Contract\TaskDatabaseRepositoryInterface;
use App\Models\Task\Enum\TaskStatus;
use App\Models\Task\Exception\InvalidTaskStatusException;
use App\Models\Task\Exception\TaskNotFoundException;
use App\Models\Task\Task;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RetrySingleTaskUseCaseTest extends TestCase
{
    public function testExecuteSuccess(): void
    {
        $taskId = 123;

        $taskRepository = $this->createMock(TaskDatabaseRepositoryInterface::class);
        $outboxRepository = $this->createMock(OutboxEventDatabaseRepositoryInterface::class);
        $deduplicationService = $this->createMock(DeduplicationServiceInterface::class);
        $em = $this->createMock(EntityManagerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $task = $this->createMock(Task::class);

        $taskRepository->expects($this->once())
            ->method('getById')
            ->with($taskId)
            ->willReturn($task);

        $task->method('getStatus')->willReturn(TaskStatus::pending);

        $em->expects($this->once())
            ->method('wrapInTransaction')
            ->willReturnCallback(function ($callback) {
                $callback();
            });

        $deduplicationService->expects($this->once())
            ->method('release')
            ->with($taskId);

        $outboxRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($event) use ($taskId) {
                return $event->getAggregateId() === $taskId;
            }));

        $task->expects($this->once())->method('setNextRetryAt')->with(null);
        $task->expects($this->once())->method('markPending');

        $taskRepository->expects($this->once())
            ->method('save')
            ->with($task);

        $em->expects($this->once())->method('flush');

        $logger->expects($this->once())
            ->method('info')
            ->with("Manual retry triggered for task {$taskId}");

        $useCase = new RetrySingleTaskUseCase(
            $taskRepository,
            $outboxRepository,
            $deduplicationService,
            $em,
            $logger
        );

        $useCase->execute($taskId);
    }

    public function testExecuteTaskNotFound(): void
    {
        $taskRepository = $this->createMock(TaskDatabaseRepositoryInterface::class);
        $outboxRepository = $this->createMock(OutboxEventDatabaseRepositoryInterface::class);
        $deduplicationService = $this->createMock(DeduplicationServiceInterface::class);
        $em = $this->createMock(EntityManagerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $taskRepository->method('getById')->willReturn(null);

        $useCase = new RetrySingleTaskUseCase(
            $taskRepository,
            $outboxRepository,
            $deduplicationService,
            $em,
            $logger
        );

        $this->expectException(TaskNotFoundException::class);

        $useCase->execute(123);
    }

    public function testExecuteTaskIsRunning(): void
    {
        $taskRepository = $this->createMock(TaskDatabaseRepositoryInterface::class);
        $outboxRepository = $this->createMock(OutboxEventDatabaseRepositoryInterface::class);
        $deduplicationService = $this->createMock(DeduplicationServiceInterface::class);
        $em = $this->createMock(EntityManagerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $task = $this->createMock(Task::class);

        $taskRepository->method('getById')->willReturn($task);

        $task->method('getStatus')->willReturn(TaskStatus::running);

        $useCase = new RetrySingleTaskUseCase(
            $taskRepository,
            $outboxRepository,
            $deduplicationService,
            $em,
            $logger
        );

        $this->expectException(InvalidTaskStatusException::class);

        $useCase->execute(123);
    }

    public function testNoSideEffectsWhenExceptionThrown(): void
    {
        $taskRepository = $this->createMock(TaskDatabaseRepositoryInterface::class);
        $outboxRepository = $this->createMock(OutboxEventDatabaseRepositoryInterface::class);
        $deduplicationService = $this->createMock(DeduplicationServiceInterface::class);
        $em = $this->createMock(EntityManagerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $taskRepository->method('getById')->willReturn(null);

        $deduplicationService->expects($this->never())->method('release');
        $outboxRepository->expects($this->never())->method('save');
        $em->expects($this->never())->method('wrapInTransaction');
        $em->expects($this->never())->method('flush');

        $useCase = new RetrySingleTaskUseCase(
            $taskRepository,
            $outboxRepository,
            $deduplicationService,
            $em,
            $logger
        );

        try {
            $useCase->execute(123);
        } catch (TaskNotFoundException) {
        }
    }
}
