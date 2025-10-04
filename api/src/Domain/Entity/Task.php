<?php
namespace Taskboard\Domain\Entity;

final class Task
{
    public function __construct(
        private int    $id,
        private string $title,
        private string $status,
        private string $createdAt
    ) {}

    // getters
    public function getId(): int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getStatus(): string { return $this->status; }
    public function getCreatedAt(): string { return $this->createdAt; }

    // domain rules (validation)
    public function setTitle(string $title): void {
        $title = trim($title);
        if ($title === '') throw new \InvalidArgumentException('title is required');
        if (mb_strlen($title) > 255) throw new \InvalidArgumentException('title too long');
        $this->title = $title;
    }

    public function setStatus(string $status): void {
        if (!in_array($status, ['pending','done'], true)) {
            throw new \InvalidArgumentException('invalid status (pending|done)');
        }
        $this->status = $status;
    }

    // presenter
    public function toArray(): array {
        return [
            'id'         => $this->id,
            'title'      => $this->title,
            'status'     => $this->status,
            'created_at' => $this->createdAt,
        ];
    }
}
