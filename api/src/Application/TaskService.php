<?php
namespace Taskboard\Application;

use Taskboard\Domain\Entity\Task;

class TaskService
{
 
    public function normalizeCreateInput(array $input): string {
        $title = trim((string)($input['title'] ?? ''));
        if ($title === '') throw new \InvalidArgumentException('title is required');
        if (mb_strlen($title) > 255) throw new \InvalidArgumentException('title too long');
        return $title;
    }




    /** @return array{title:?string,status:?string} */
    public function normalizeUpdateInput(array $input): array {
        $title  = array_key_exists('title',  $input) ? trim((string)$input['title']) : null;
        $status = array_key_exists('status', $input) ? trim((string)$input['status']) : null;

        if ($title !== null) {
            if ($title === '') throw new \InvalidArgumentException('title is required');
            if (mb_strlen($title) > 255) throw new \InvalidArgumentException('title too long');
        }
        if ($status !== null && !in_array($status, ['pending','done'], true)) {
            throw new \InvalidArgumentException('invalid status (pending|done)');
        }
        return ['title'=>$title, 'status'=>$status];
    }




    
    public function present(Task $task): array { return $task->toArray(); }
    /** @param Task[] $tasks */ 
    public function presentMany(array $tasks): array { return array_map(fn(Task $t) => $t->toArray(), $tasks); }
}
