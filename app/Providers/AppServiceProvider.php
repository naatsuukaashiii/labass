<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Observers\UserObserver;
use App\Observers\RoleObserver;
use App\Observers\PermissionObserver;
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }
    public function boot(): void
    {
        User::observe(UserObserver::class);
        Role::observe(RoleObserver::class);
        Permission::observe(PermissionObserver::class);
    }
}