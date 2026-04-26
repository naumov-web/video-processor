<?php

namespace App\Controller\Web;

use App\Models\Common\Filter\BaseFilter;
use App\UseCase\Task\GetTasksUseCase;
use App\UseCase\Task\Input\GetTasksInputDTO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TasksController extends AbstractController
{
    #[Route('/admin/tasks', name: 'admin_tasks', methods: ['GET'])]
    public function index(Request $request, GetTasksUseCase $getTasksUseCase): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = (int) $request->query->get('limit', BaseFilter::DEFAULT_LIMIT);
        $sortBy = (string) $request->query->get('sort', 'createdAt');
        $direction = (string) $request->query->get('direction', 'desc');
        $status = $request->query->get('status');
        $type = $request->query->get('type');
        $offset = ($page - 1) * $limit;

        $inputDto = new GetTasksInputDTO(
            offset: $offset,
            limit: $limit,
            sortBy: $sortBy,
            direction: $direction,
            status: empty($status) ? null : $status,
            type: empty($type) ? null : $type,
        );

        $paginatedResult = $getTasksUseCase->execute(
            $inputDto
        );
        $pages = (int) ceil($paginatedResult->total / $limit);

        return $this->render('admin/tasks/index.html.twig', [
            'tasks' => $paginatedResult->items,
            'total' => $paginatedResult->total,
            'page' => $page,
            'pages' => $pages,
            'limit' => $limit,
            'sort' => $sortBy,
            'direction' => $direction,
            'status' => $status,
            'type' => $type,
        ]);
    }
}
