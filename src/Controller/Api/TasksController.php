<?php

namespace App\Controller\Api;

use App\Models\Task\Enum\TaskType;
use App\Models\Task\Task;
use App\Models\Task\Validator\CreateTaskValidator;
use App\Models\Task\Validator\GetTasksValidator;
use App\UseCase\Task\CreateTaskUseCase;
use App\UseCase\Task\GetTasksUseCase;
use App\UseCase\Task\Input\CreateTaskInputDTO;
use App\UseCase\Task\Input\GetTasksInputDTO;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/tasks')]
class TasksController extends AbstractController
{
    public function __construct(
        private CreateTaskUseCase $createTaskUseCase,
        private GetTasksUseCase $getTasksUseCase,
        private CreateTaskValidator $createTaskValidator,
        private GetTasksValidator $getTasksValidator,
    ) {}

    #[Route('', methods: ['POST'])]
    #[OA\Post(
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
        $this->createTaskValidator->validate($data);
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

    #[Route('', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get list of video tasks',
        parameters: [
            new OA\Parameter(
                name: 'limit',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 20, minimum: 1, maximum: 100)
            ),
            new OA\Parameter(
                name: 'offset',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 0, minimum: 0)
            ),
            new OA\Parameter(
                name: 'sortBy',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['id', 'videoId', 'status', 'type', 'createdAt'], default: 'createdAt')
            ),
            new OA\Parameter(
                name: 'direction',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'], default: 'desc')
            ),
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['pending', 'running', 'completed', 'failed'])
            ),
            new OA\Parameter(
                name: 'type',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['transcode', 'thumbnail', 'ai_tagging'])
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of tasks',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'items',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer'),
                                    new OA\Property(property: 'videoId', type: 'integer'),
                                    new OA\Property(property: 'type', type: 'string'),
                                    new OA\Property(property: 'status', type: 'string'),
                                    new OA\Property(property: 'priority', type: 'integer'),
                                    new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                                ]
                            )
                        ),
                        new OA\Property(property: 'count', type: 'integer'),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            )
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $data = [
            'limit' => $request->query->getInt('limit'),
            'offset' => $request->query->getInt('offset'),
            'sortBy' => $request->query->get('sortBy'),
            'direction' => $request->query->get('direction'),
            'status' => $request->query->get('status'),
            'type' => $request->query->get('type'),
        ];
        $this->getTasksValidator->validate($data);
        $input = new GetTasksInputDTO(
            offset: $data['offset'] ?? null,
            limit: $data['limit'] ?? null,
            sortBy: $data['sortBy'] ?? null,
            direction: $data['direction'] ?? null,
            status: $data['status'] ?? null,
            type: $data['type'] ?? null,
        );
        $paginatedResult = $this->getTasksUseCase->execute($input);

        return new JsonResponse([
            'items' => array_map(
                fn(Task $task) => [
                    'id' => $task->getId(),
                    'videoId' => $task->getVideoId(),
                    'type' => $task->getType()->value,
                    'status' => $task->getStatus()->value,
                    'priority' => $task->getPriority(),
                    'createdAt' => $task->getCreatedAt()->format(DATE_ATOM),
                ], $paginatedResult->items->toArray()),
            'total' => $paginatedResult->total,
        ]);
    }
}
