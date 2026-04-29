<?php

namespace App\Controller\Api;

use App\UseCase\User\LoginUserUseCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[Route('/api/auth')]
class AuthController
{
    public function __construct(
        private readonly LoginUserUseCase $useCase,
    ) {}

    #[Route('/login', methods: ['POST'])]
    #[OA\Post(
        summary: 'User login',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'email', type: 'string'),
                    new OA\Property(property: 'password', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'JWT token',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'accessToken', type: 'string')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Invalid credentials')
        ]
    )]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $token = $this->useCase->execute(
                $data['email'] ?? '',
                $data['password'] ?? ''
            );

            return new JsonResponse([
                'accessToken' => $token,
            ]);

        } catch (\Throwable $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
}
