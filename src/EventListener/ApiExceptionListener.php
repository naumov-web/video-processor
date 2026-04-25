<?php

namespace App\EventListener;

use App\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ApiExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof ValidationException) {
            return;
        }

        $errors = [];

        foreach ($exception->getErrors() as $error) {
            $field = trim($error->getPropertyPath(), '[]');
            $errors[$field][] = $error->getMessage();
        }

        $response = new JsonResponse([
            'errors' => $errors
        ], Response::HTTP_UNPROCESSABLE_ENTITY);

        $event->setResponse($response);
    }
}
