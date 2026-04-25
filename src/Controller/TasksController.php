<?php

namespace App\Controller;

use App\Exception\ValidationException;
use App\Models\Task\Enum\TaskType;
use App\Models\Task\Validator\CreateVideoTaskValidator;
use App\UseCase\Task\CreateTaskUseCase;
use App\UseCase\Task\Input\CreateTaskInputDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class TasksController
{
    public function __construct(
        private CreateTaskUseCase $createTaskUseCase,
        private CreateVideoTaskValidator $createVideoTaskValidator,
    ) {}

    #[Route('/api/tasks', methods: ['POST'])]
    #[OA\Post(
        path: '/api/tasks',
        summary: 'Create video processing task',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['videoId', 'type', 'inputData'],
                properties: [
                    new OA\Property(property: 'videoId', type: 'integer', example: 123),
                    new OA\Property(property: 'type', type: 'string', example: 'transcode'),
                    new OA\Property(
                        property: 'inputData',
                        type: 'object',
                        example: [
                            'filePath' => '/videos/raw/123.mp4',
                            'formats' => ['720p', '1080p']
                        ]
                    ),
                    new OA\Property(property: 'source', type: 'string', example: 'api'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Task created',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'status', type: 'string', example: 'pending'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request'
            )
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $this->createVideoTaskValidator->validate($data);
        $input = new CreateTaskInputDTO(
            videoId: $data['videoId'],
            type: TaskType::from($data['type']),
            inputData: $data['inputData'],
            source: $data['source']
        );

        $task = $this->createTaskUseCase->execute($input);

        return new JsonResponse(
            [
                'id' => $task->getId(),
                'status' => $task->getStatus()->value,
            ],
            Response::HTTP_CREATED
        );
    }
}
