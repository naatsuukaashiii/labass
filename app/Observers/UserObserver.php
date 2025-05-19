<?php
namespace App\Observers;
use App\Models\User;
use App\Models\ChangeLog;
class UserObserver
{
    public function created(User $user)
    {
        ChangeLog::create([
            'entity_type' => 'User',
            'entity_id' => $user->id,
            'before' => null,
            'after' => $user->toArray(),
        ]);
    }
    public function updated(User $user)
    {
        ChangeLog::create([
            'entity_type' => 'User',
            'entity_id' => $user->id,
            'before' => $user->getOriginal(),
            'after' => $user->getAttributes(),
        ]);
    }
    public function deleted(User $user)
    {
        ChangeLog::create([
            'entity_type' => 'User',
            'entity_id' => $user->id,
            'before' => $user->getOriginal(),
            'after' => null,
        ]);
    }
}