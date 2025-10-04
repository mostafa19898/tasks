<?php
namespace Taskboard\Infrastructure\Repository;

use PDO;
use Taskboard\Infrastructure\Database\Connection;
use Taskboard\Domain\Entity\Task;

class PdoTaskRepository
{
    public function __construct(private ?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Connection::make();
    }

    /** @return Task[] */

    public function all(): array {
        $stmt = $this->pdo->query('SELECT id,title,status,created_at FROM tasks ORDER BY id DESC');
        $rows = $stmt->fetchAll();
        return array_map([$this,'map'], $rows);
    }

    public function find(int $id): ?Task {
        $stmt = $this->pdo->prepare('SELECT id,title,status,created_at FROM tasks WHERE id = :id');
        $stmt->execute(['id'=>$id]);
        $row = $stmt->fetch();
        return $row ? $this->map($row) : null;
    }

    public function create(string $title): Task {
        $stmt = $this->pdo->prepare('INSERT INTO tasks (title) VALUES (:title)');
        $stmt->execute(['title'=>$title]);
        $id = (int)$this->pdo->lastInsertId();


        return $this->find($id);
    }

    public function update(int $id, ?string $title = null, ?string $status = null): ?Task {
        $fields = []; $params = [];
        if ($title !== null)  { $fields[] = 'title = :title';   $params['title']  = $title; }
        if ($status !== null) { $fields[] = 'status = :status'; $params['status'] = $status; }
        if ($fields) {
            $params['id'] = $id;
            $sql = 'UPDATE tasks SET '.implode(', ', $fields).' WHERE id = :id';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
        }
        return $this->find($id);
    }

    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare('DELETE FROM tasks WHERE id = :id');
        $stmt->execute(['id'=>$id]);
        return $stmt->rowCount() > 0;
    }

    /** @param array{id:mixed,title:string,status:string,created_at:string} $row */


    private function map(array $row): Task {
        return new Task(
            (int)$row['id'],
            (string)$row['title'],
            (string)$row['status'],
            (string)$row['created_at'],
        );
    }

        public static function make(): self
        {
            return new self();
        }
}
