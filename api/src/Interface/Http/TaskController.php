<?php
namespace Taskboard\Interface\Http;

use Taskboard\Application\TaskService;
use Taskboard\Infrastructure\Repository\PdoTaskRepository;

class TaskController
{
    private TaskService $service;
    private PdoTaskRepository $repo;

    public function __construct()
    {
        $this->repo    = new PdoTaskRepository();
        $this->service = new TaskService();
    }

    public function index(): array
    {
        return $this->service->presentMany($this->repo->all());
    }

    public function store(array $input): array
    {
        $title = $this->service->normalizeCreateInput($input);  
        $task  = $this->repo->create($title);
        return $this->service->present($task);
    }

    public function update(int $id, array $input): array
    {
        $norm  = $this->service->normalizeUpdateInput($input);  
        $task  = $this->repo->update($id, $norm['title'], $norm['status']);
        if (!$task) { throw new \InvalidArgumentException('Task not found'); }
        return $this->service->present($task);
    }

    public function destroy(int $id): bool
    {
        $ok = $this->repo->delete($id);
        if (!$ok) { throw new \InvalidArgumentException('Task not found'); }

        return true;
    }
}
