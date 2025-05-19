<?php
namespace App\Observers;
use App\Models\Permission;
use App\Models\ChangeLog;
class PermissionObserver
{
    public function created(Permission $permission)
    {
        ChangeLog::create([
            'entity_type' => 'Permission',
            'entity_id' => $permission->id,
            'before' => null,
            'after' => $permission->getAttributes(),
        ]);
    }
    public function updated(Permission $permission)
    {
        ChangeLog::create([
            'entity_type' => 'Permission',
            'entity_id' => $permission->id,
            'before' => $permission->getOriginal(),
            'after' => $permission->getAttributes(),
        ]);
    }
    public function deleted(Permission $permission)
    {
        ChangeLog::create([
            'entity_type' => 'Permission',
            'entity_id' => $permission->id,
            'before' => $permission->getOriginal(),
            'after' => null,
        ]);
    }
    public function restored(Permission $permission)
    {
        ChangeLog::create([
            'entity_type' => 'Permission',
            'entity_id' => $permission->id,
            'before' => null,
            'after' => $permission->getAttributes(),
        ]);
    }
}