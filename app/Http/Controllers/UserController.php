<?php
namespace App\Http\Controllers;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\DTO\UserDTO;
use App\DTO\UserCollectionDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
class UserController extends Controller
{
    public function index(): JsonResponse
    {
        if (!auth()->user()->hasPermission('get-list-user')) {
            return response()->json(['message' => 'Permission denied: get-list-user'], 403);
        }
        $users = User::with('roles')->get();
        return response()->json(new UserCollectionDTO(
            $users->map(function ($user) {
                return new UserDTO(
                    id: $user->id,
                    username: $user->username,
                    email: $user->email,
                    birthday: $user->birthday,
                    roles: $user->roles->map(fn($role) => [
                        'id' => $role->id,
                        'name' => $role->name,
                        'code' => $role->code,
                    ])->toArray()
                );
            })->toArray()
        ));
    }
    public function store(StoreUserRequest $request): JsonResponse
    {
        if (!auth()->user()->hasPermission('create-user')) {
            return response()->json(['message' => 'Permission denied: create-user'], 403);
        }
        try {
            DB::beginTransaction();
            $user = User::create(array_merge($request->validated(), [
                'password' => bcrypt($request->input('password')),
                'created_by' => auth()->id(),
            ]));
            if ($request->has('roles')) {
                $user->roles()->attach($request->input('roles'), ['created_by' => auth()->id()]);
            }
            DB::commit();
            return response()->json(new UserDTO(
                id: $user->id,
                username: $user->username,
                email: $user->email,
                birthday: $user->birthday,
                roles: []
            ), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating user', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to create user'], 500);
        }
    }
    public function show($id): JsonResponse
    {
        if (!auth()->user()->hasPermission('read-user')) {
            return response()->json(['message' => 'Permission denied: read-user'], 403);
        }
        $user = User::with('roles')->find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json(new UserDTO(
            id: $user->id,
            username: $user->username,
            email: $user->email,
            birthday: $user->birthday,
            roles: $user->roles->map(fn($role) => [
                'id' => $role->id,
                'name' => $role->name,
                'code' => $role->code,
            ])->toArray()
        ));
    }
    public function update(UpdateUserRequest $request, $id): JsonResponse
    {
        if (!auth()->user()->hasPermission('update-user')) {
            return response()->json(['message' => 'Permission denied: update-user'], 403);
        }
        try {
            DB::beginTransaction();
            $user = User::find($id);
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }
            $user->update($request->validated());
            if ($request->has('roles')) {
                $user->roles()->sync($request->input('roles'), ['updated_by' => auth()->id()]);
            }
            DB::commit();
            return response()->json(new UserDTO(
                id: $user->id,
                username: $user->username,
                email: $user->email,
                birthday: $user->birthday,
                roles: $user->roles->map(fn($role) => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'code' => $role->code,
                ])->toArray()
            ));
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating user', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to update user'], 500);
        }
    }
    public function destroy($userId): JsonResponse
    {
        if (!auth()->user()->hasPermission('delete-user')) {
            return response()->json(['message' => 'Permission denied: delete-user'], 403);
        }
        try {
            DB::beginTransaction();
            $user = User::withTrashed()->find($userId);
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }
            $user->forceDelete();
            DB::commit();
            return response()->json(['message' => 'User permanently deleted']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error deleting user', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to delete user'], 500);
        }
    }
    public function softDelete($id): JsonResponse
    {
        if (!auth()->user()->hasPermission('delete-user')) {
            return response()->json(['message' => 'Permission denied: delete-user'], 403);
        }
        try {
            DB::beginTransaction();
            $user = User::find($id);
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }
            $user->update([
                'deleted_at' => now(),
                'deleted_by' => auth()->id(),
            ]);
            DB::commit();
            return response()->json(['message' => 'User softly deleted']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error softly deleting user', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to softly delete user'], 500);
        }
    }
    public function restore($id): JsonResponse
    {
        if (!auth()->user()->hasPermission('restore-user')) {
            return response()->json(['message' => 'Permission denied: restore-user'], 403);
        }
        try {
            DB::beginTransaction();
            $user = User::withTrashed()->find($id);
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }
            $user->update([
                'deleted_at' => null,
                'deleted_by' => null,
            ]);
            DB::commit();
            return response()->json(['message' => 'User restored']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error restoring user', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to restore user'], 500);
        }
    }
}