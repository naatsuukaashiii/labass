<?php
namespace Database\Seeders;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;
class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $adminUser = User::create([
            'username' => 'Arinaaa',
            'email' => 'arina@mail.ru',
            'password' => bcrypt('Password123!'),
            'birthday' => '2005-07-18!',
            'created_by' => null,
        ]);
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
        ]);
        $adminRole = Role::where('code', 'admin')->first();
        $adminUser->roles()->attach($adminRole);

        $permissions = Permission::all();
        $adminRole->permissions()->attach($permissions);
    }
}