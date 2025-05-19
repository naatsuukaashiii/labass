<?php
namespace App\Http\Controllers;
use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Models\Permission;
use App\DTO\PermissionDTO;
use App\DTO\PermissionCollectionDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
class PermissionController extends Controller
{
    public function index(): JsonResponse
    {
        if (!auth()->user()->hasPermission('get-list-permission')) {
            return response()->json(['message' => 'Permission denied: get-list-permission'], 403);
        }
        $permissions = Permission::all();
        return response()->json(new PermissionCollectionDTO(
            $permissions->map(function ($permission) {
                return new PermissionDTO(
                    id: $permission->id,
                    name: $permission->name,
                    description: $permission->description,
                    code: $permission->code
                );
            })->toArray()
        ));
    }
    public function store(StorePermissionRequest $request): JsonResponse
    {
        if (!auth()->user()->hasPermission('create-permission')) {
            return response()->json(['message' => 'Permission denied: create-permission'], 403);
        }
        try {
            DB::beginTransaction();
            $permission = Permission::create(array_merge($request->validated(), [
                'created_by' => auth()->id(),
            ]));
            DB::commit();
            return response()->json(new PermissionDTO(
                id: $permission->id,
                name: $permission->name,
                description: $permission->description,
                code: $permission->code
            ), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating permission', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to create permission'], 500);
        }
    }
    public function show($permissionId): JsonResponse
    {
        if (!auth()->user()->hasPermission('read-permission')) {
            return response()->json(['message' => 'Permission denied: read-permission'], 403);
        }
        $permission = Permission::find($permissionId);
        if (!$permission) {
            return response()->json(['message' => 'Permission not found'], 404);
        }
        return response()->json(new PermissionDTO(
            id: $permission->id,
            name: $permission->name,
            description: $permission->description,
            code: $permission->code
        ));
    }
    public function update(UpdatePermissionRequest $request, $permissionId): JsonResponse
    {
        if (!auth()->user()->hasPermission('update-permission')) {
            return response()->json(['message' => 'Permission denied: update-permission'], 403);
        }
        try {
            DB::beginTransaction();
            $permission = Permission::find($permissionId);
            if (!$permission) {
                return response()->json(['message' => 'Permission not found'], 404);
            }
            $permission->update($request->validated());
            DB::commit();
            return response()->json(new PermissionDTO(
                id: $permission->id,
                name: $permission->name,
                description: $permission->description,
                code: $permission->code
            ));
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating permission', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to update permission'], 500);
        }
    }
    public function destroy($permissionId): JsonResponse
    {
        if (!auth()->user()->hasPermission('delete-permission')) {
            return response()->json(['message' => 'Permission denied: delete-permission'], 403);
        }
        try {
            DB::beginTransaction();
            $permission = Permission::withTrashed()->find($permissionId);
            if (!$permission) {
                return response()->json(['message' => 'Permission not found'], 404);
            }
            $permission->forceDelete();
            DB::commit();
            return response()->json(['message' => 'Permission permanently deleted']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error deleting permission', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to delete permission'], 500);
        }
    }
    public function softDelete($permissionId): JsonResponse
    {
        if (!auth()->user()->hasPermission('delete-permission')) {
            return response()->json(['message' => 'Permission denied: delete-permission'], 403);
        }
        try {
            DB::beginTransaction();
            $permission = Permission::find($permissionId);
            if (!$permission) {
                return response()->json(['message' => 'Permission not found'], 404);
            }
            if ($permission->trashed()) {
                return response()->json(['message' => 'Permission is already softly deleted'], 400);
            }
            $permission->delete();
            DB::commit();
            return response()->json(['message' => 'Permission softly deleted']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error softly deleting permission', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to softly delete permission'], 500);
        }
    }
    public function restore($permissionId): JsonResponse
    {
        if (!auth()->user()->hasPermission('restore-permission')) {
            return response()->json(['message' => 'Permission denied: restore-permission'], 403);
        }
        try {
            DB::beginTransaction();

            $permission = Permission::withTrashed()->find($permissionId);
            if (!$permission) {
                return response()->json(['message' => 'Permission not found'], 404);
            }
            if (!$permission->trashed()) {
                return response()->json(['message' => 'Permission is not softly deleted'], 400);
            }
            $permission->restore();
            DB::commit();
            return response()->json(['message' => 'Permission restored']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error restoring permission', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to restore permission'], 500);
        }
    }
}