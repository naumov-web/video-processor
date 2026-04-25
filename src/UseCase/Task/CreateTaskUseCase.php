<?php

namespace App\UseCase\Task;

use App\Models\Task\Contract\TaskDatabaseRepositoryInterface;
use App\Models\Task\Exception\ActiveTaskAlreadyExistsException;
use App\Models\Task\Task;
use App\UseCase\Task\Input\CreateTaskInputDTO;
use Doctrine\ORM\EntityManagerInterface;

class CreateTaskUseCase
{
    public function __construct(
        private TaskDatabaseRepositoryInterface $repository,
        private EntityManagerInterface          $em,
    ) {}

    public function execute(CreateTaskInputDTO $input): Task
    {
        if ($this->repository->existsActiveTaskForVideo($input->videoId, $input->type)) {
            throw new ActiveTaskAlreadyExistsException('Active task already exists for this video');
        }

        $task = new Task(
            videoId: $input->videoId,
            type: $input->type,
            inputData: $input->inputData,
            source: $input->source,
            processingKey: $this->getProcessingKey($input),
        );
        $this->em->beginTransaction();

        try {
            $this->repository->save($task);
            $this->em->commit();

            return $task;
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }
    }

    private function getProcessingKey(CreateTaskInputDTO $input): string
    {
        return $input->videoId . '-' . $input->type->value . '-' . $input->source;
    }
}
