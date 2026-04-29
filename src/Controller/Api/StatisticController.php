<?php

namespace App\Controller\Api;

use App\UseCase\Statistic\GetVideoStatisticUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Attributes as OA;

#[Route('/api/statistic')]
class StatisticController extends AbstractController
{
    public function __construct(
        private readonly GetVideoStatisticUseCase $getVideoStatisticUseCase,
        private readonly SerializerInterface $serializer,
    ) {}

    #[Route('/videos/{videoId}', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get statistic for video',
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(
                name: 'videoId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Video task statistics',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'task_id', type: 'integer', example: 123),
                            new OA\Property(property: 'status', type: 'string', example: 'failed'),
                            new OA\Property(property: 'type', type: 'string', example: 'failed'),
                            new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-04-29 12:00:00'),
                            new OA\Property(property: 'attempt_number', type: 'integer', example: 2),
                            new OA\Property(property: 'prev_attempt_at', type: 'string', format: 'date-time', example: '2026-04-29 11:58:00', nullable: true),
                            new OA\Property(property: 'retry_delay', type: 'string', example: '00:02:00', nullable: true),
                            new OA\Property(property: 'total_tasks', type: 'integer', example: 3),
                            new OA\Property(property: 'success_count', type: 'integer', example: 1),
                            new OA\Property(property: 'failed_count', type: 'integer', example: 2),
                        ],
                        type: 'object'
                    )
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            ),
        ]
    )]
    public function getForVideo(int $videoId): JsonResponse
    {
        $dtos = $this->getVideoStatisticUseCase->execute($videoId);
        $json = $this->serializer->serialize($dtos, 'json');

        return new JsonResponse($json, 200, [], true);
    }
}
