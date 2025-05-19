<?php
namespace Database\Seeders;
use App\Models\Role;
use Illuminate\Database\Seeder;
class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            ['name' => 'Admin', 'code' => 'admin', 'description' => 'Administrator role'],
            ['name' => 'User', 'code' => 'user', 'description' => 'Regular user role'],
            ['name' => 'Guest', 'code' => 'guest', 'description' => 'Guest role'],
        ];
        foreach ($roles as $roleData) {
            Role::create(array_merge($roleData, [
                'created_by' => 1,
            ]));
        }
    }
}