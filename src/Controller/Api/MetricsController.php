<?php

namespace App\Controller\Api;

use App\Infrastructure\Metrics\MetricsService;
use Prometheus\RenderTextFormat;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

#[Route('/api/metrics')]
class MetricsController  extends AbstractController
{
    public function __construct(
        private readonly MetricsService $metrics
    ) {}

    #[Route('', methods: ['GET'])]
    public function metrics(): Response
    {
        $renderer = new RenderTextFormat();
        $result = $renderer->render(
            $this->metrics->getRegistry()->getMetricFamilySamples()
        );

        return new Response($result, 200, [
            'Content-Type' => RenderTextFormat::MIME_TYPE,
        ]);
    }
}
