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
            'username' => 'JohnDoe',
            'email' => 'johndoe@mail.ru',
            'password' => bcrypt('Password123!'),
            'birthday' => '2005-11-16!',
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