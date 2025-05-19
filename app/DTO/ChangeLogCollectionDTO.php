<?php
namespace App\DTO;
class ChangeLogCollectionDTO
{
    public function __construct(
        public array $logs,
    ) {}
    public function toArray(): array
    {
        return [
            'logs' => $this->logs,
        ];
    }
}