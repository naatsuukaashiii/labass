<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register'])->middleware('guest');
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/out', [AuthController::class, 'logout']);
    Route::get('/auth/tokens', [AuthController::class, 'tokens']);
    Route::post('/auth/out_all', [AuthController::class, 'logoutAll']);
    Route::post('/auth/change_password', [AuthController::class, 'changePassword']);
});

use App\Http\Controllers\UserController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\PermissionController;

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('ref/user')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('user.index');
        Route::post('/', [UserController::class, 'store'])->name('user.store');
        Route::get('/{id}', [UserController::class, 'show'])->name('user.show');
        Route::put('/{id}', [UserController::class, 'update'])->name('user.update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('user.destroy');
        Route::delete('/{id}/soft', [UserController::class, 'softDelete'])->name('user.soft-delete');
        Route::post('/{id}/restore', [UserController::class, 'restore'])->name('user.restore');
    });

    Route::prefix('ref/user/{userId}/role')->group(function () {
        Route::get('/', [UserRoleController::class, 'index'])->name('user.role.index');
        Route::post('/', [UserRoleController::class, 'store'])->name('user.role.store');
        Route::delete('/', [UserRoleController::class, 'destroy'])->name('user.role.destroy');
        Route::delete('/{roleId}/soft', [UserRoleController::class, 'softDelete'])->name('user.role.soft-delete');
        Route::post('/{roleId}/restore', [UserRoleController::class, 'restore'])->name('user.role.restore');
        Route::get('/permissions', [UserRoleController::class, 'getPermissions'])->name('user.role.permissions');
    });

    Route::prefix('ref/role')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('role.index');
        Route::post('/', [RoleController::class, 'store'])->name('role.store');
        Route::get('/{id}', [RoleController::class, 'show'])->name('role.show');
        Route::put('/{id}', [RoleController::class, 'update'])->name('role.update');
        Route::delete('/{id}', [RoleController::class, 'destroy'])->name('role.destroy');
        Route::delete('/{id}/soft', [RoleController::class, 'softDelete'])->name('role.soft-delete');
        Route::post('/{id}/restore', [RoleController::class, 'restore'])->name('role.restore');
    });

    Route::prefix('ref/role/{role}/permission')->group(function () {
        Route::get('/', [RolePermissionController::class, 'index'])->name('role.permission.index');
        Route::post('/', [RolePermissionController::class, 'store'])->name('role.permission.store');
        Route::delete('/', [RolePermissionController::class, 'destroy'])->name('role.permission.destroy');
        Route::delete('/{permission}/soft', [RolePermissionController::class, 'softDelete'])->name('role.permission.soft-delete');
        Route::post('/{permission}/restore', [RolePermissionController::class, 'restore'])->name('role.permission.restore');
        Route::get('/users', [RolePermissionController::class, 'getUsers'])->name('role.permission.users');
    });

    Route::prefix('ref/permission')->group(function () {
        Route::get('/', [PermissionController::class, 'index'])->name('permission.index');
        Route::post('/', [PermissionController::class, 'store'])->name('permission.store');
        Route::get('/{id}', [PermissionController::class, 'show'])->name('permission.show');
        Route::put('/{id}', [PermissionController::class, 'update'])->name('permission.update');
        Route::delete('/{id}', [PermissionController::class, 'destroy'])->name('permission.destroy');
        Route::delete('/{id}/soft', [PermissionController::class, 'softDelete'])->name('permission.soft-delete');
        Route::post('/{id}/restore', [PermissionController::class, 'restore'])->name('permission.restore');
    });
});

use App\Http\Controllers\ChangeLogController;

Route::prefix('ref')->middleware(['auth:sanctum'])->group(function () {
    Route::prefix('user/{userId}/story')->group(function () {
        Route::get('/', [ChangeLogController::class, 'getUserHistory'])->name('user.history');
    });
    Route::prefix('/policy/role/{roleId}/story')->group(function () {
        Route::get('/', [ChangeLogController::class, 'getRoleHistory'])->name('role.history');
    });
    Route::prefix('/policy/permission/{permissionId}/story')->group(function () {
        Route::get('/', [ChangeLogController::class, 'getPermissionHistory'])->name('permission.history');
    });
    Route::post('/change-log/{logId}/restore', [ChangeLogController::class, 'restoreEntityState'])->name('change-log.restore');
});