<?php

namespace App\Controller\Api;

use App\UseCase\Health\HealthCheckUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[Route('/api/health')]
class HealthController  extends AbstractController
{
    public function __construct(
        private readonly HealthCheckUseCase $healthCheckUseCase,
    ) {}

    #[Route('', methods: ['GET'])]
    #[OA\Get(
        summary: 'Health check endpoint',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'checks', properties: [
                            new OA\Property(property: 'database', type: 'boolean', example: true),
                            new OA\Property(property: 'redis', type: 'boolean', example: true),
                            new OA\Property(property: 'kafka', type: 'boolean', example: true),
                        ], type: 'object'),
                    ]
                )
            ),
            new OA\Response(
                response: 503,
                description: 'Service unavailable',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'checks', properties: [
                            new OA\Property(property: 'database', type: 'boolean', example: true),
                            new OA\Property(property: 'redis', type: 'boolean', example: true),
                            new OA\Property(property: 'kafka', type: 'boolean', example: true),
                        ], type: 'object'),
                    ]
                )
            ),
        ]
    )]
    public function check(): Response
    {
        $dto = $this->healthCheckUseCase->execute();

        return $this->json(
            [
                'success' => $dto->success,
                'checks' => $dto->checks,
            ],
            $dto->success ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE
        );
    }
}
