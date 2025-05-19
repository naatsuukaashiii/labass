<?php
namespace App\DTO;
class PermissionCollectionDTO
{
    public function __construct(public array $permissions) {}
    public function toArray()
    {
        return ['permissions' => $this->permissions];
    }
}