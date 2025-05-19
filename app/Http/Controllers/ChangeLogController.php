<?php
namespace App\Http\Controllers;
use App\Models\ChangeLog;
use App\DTO\ChangeLogDTO;
use App\DTO\ChangeLogCollectionDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\Permission;
use App\Models\Role;
class ChangeLogController extends Controller
{
    public function getUserHistory($userId): JsonResponse
    {
        if (!auth()->user()->hasPermission('get-story-user')) {
            return response()->json(['message' => 'Permission denied: get-story-user'], 403);
        }
        $logs = ChangeLog::where('entity_type', 'User')
            ->where('entity_id', $userId)
            ->get();
        return response()->json(new ChangeLogCollectionDTO(
            $logs->map(function ($log) {
                $before = $log->before ?? [];
                $after = $log->after ?? [];
                $changedProperties = array_filter($after, function ($value, $key) use ($before) {
                    return !isset($before[$key]) || $before[$key] !== $value;
                }, ARRAY_FILTER_USE_BOTH);
                return new ChangeLogDTO(
                    id: $log->id,
                    entityType: $log->entity_type,
                    entityId: $log->entity_id,
                    before: $before,
                    after: $changedProperties,
                    createdAt: $log->created_at->toDateTimeString()
                );
            })->toArray()
        ));
    }
    public function getRoleHistory($roleId): JsonResponse
    {
        if (!auth()->user()->hasPermission('get-story-role')) {
            return response()->json(['message' => 'Permission denied: get-story-role'], 403);
        }
        $logs = ChangeLog::where('entity_type', 'Role')
            ->where('entity_id', $roleId)
            ->get();
        return response()->json(new ChangeLogCollectionDTO(
            $logs->map(function ($log) {
                $before = $log->before ?? [];
                $after = $log->after ?? [];

                $changedProperties = array_filter($after, function ($value, $key) use ($before) {
                    return !isset($before[$key]) || $before[$key] !== $value;
                }, ARRAY_FILTER_USE_BOTH);
                return new ChangeLogDTO(
                    id: $log->id,
                    entityType: $log->entity_type,
                    entityId: $log->entity_id,
                    before: $before,
                    after: $changedProperties,
                    createdAt: $log->created_at->toDateTimeString()
                );
            })->toArray()
        ));
    }
    public function getPermissionHistory($permissionId): JsonResponse
    {
        if (!auth()->user()->hasPermission('get-story-permission')) {
            return response()->json(['message' => 'Permission denied: get-story-permission'], 403);
        }
        $logs = ChangeLog::where('entity_type', 'Permission')
            ->where('entity_id', $permissionId)
            ->get();
        return response()->json(new ChangeLogCollectionDTO(
            $logs->map(function ($log) {
                $before = $log->before ?? [];
                $after = $log->after ?? [];
                $changedProperties = array_filter($after, function ($value, $key) use ($before) {
                    return !isset($before[$key]) || $before[$key] !== $value;
                }, ARRAY_FILTER_USE_BOTH);
                return new ChangeLogDTO(
                    id: $log->id,
                    entityType: $log->entity_type,
                    entityId: $log->entity_id,
                    before: $before,
                    after: $changedProperties,
                    createdAt: $log->created_at->toDateTimeString()
                );
            })->toArray()
        ));
    }
    public function restoreEntityState($logId): JsonResponse
    {
        if (!auth()->user()->hasPermission('restore-entity-state')) {
            return response()->json(['message' => 'Permission denied: restore-entity-state'], 403);
        }
        try {
            DB::beginTransaction();
            $log = ChangeLog::find($logId);
            if (!$log) {
                return response()->json(['message' => 'Log not found'], 404);
            }
            $entityType = $log->entity_type;
            $entityId = $log->entity_id;
            $beforeData = $log->before;
            if (!$beforeData) {
                return response()->json(['message' => 'No previous state available'], 400);
            }
            switch ($entityType) {
                case 'User':
                    $entity = User::find($entityId);
                    break;
                case 'Role':
                    $entity = Role::find($entityId);
                    break;
                case 'Permission':
                    $entity = Permission::find($entityId);
                    break;
                default:
                    return response()->json(['message' => 'Invalid entity type'], 400);
            }
            if (!$entity) {
                return response()->json(['message' => 'Entity not found'], 404);
            }
            $entity->update($beforeData);
            DB::commit();
            return response()->json(['message' => 'Entity restored to previous state']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error restoring entity state', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to restore entity state'], 500);
        }
    }
}