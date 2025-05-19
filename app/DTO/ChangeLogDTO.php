<?php
namespace App\DTO;
class ChangeLogDTO
{
    public function __construct(
        public int $id,
        public string $entityType,
        public int $entityId,
        public ?array $before,
        public ?array $after,
        public string $createdAt,
    ) {}
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'entity_type' => $this->entityType,
            'entity_id' => $this->entityId,
            'before' => $this->before,
            'after' => $this->after,
            'created_at' => $this->createdAt,
        ];
    }
}