<?php
namespace App\Observers;
use App\Models\Role;
use App\Models\ChangeLog;
class RoleObserver
{
    public function created(Role $role)
    {
        ChangeLog::create([
            'entity_type' => 'Role',
            'entity_id' => $role->id,
            'before' => null,
            'after' => $role->getAttributes(),
        ]);
    }
    public function updated(Role $role)
    {
        ChangeLog::create([
            'entity_type' => 'Role',
            'entity_id' => $role->id,
            'before' => $role->getOriginal(),
            'after' => $role->getAttributes(),
        ]);
    }
    public function deleted(Role $role)
    {
        ChangeLog::create([
            'entity_type' => 'Role',
            'entity_id' => $role->id,
            'before' => $role->getOriginal(),
            'after' => null,
        ]);
    }
    public function restored(Role $role)
    {
        ChangeLog::create([
            'entity_type' => 'Role',
            'entity_id' => $role->id,
            'before' => null,
            'after' => $role->getAttributes(),
        ]);
    }
}