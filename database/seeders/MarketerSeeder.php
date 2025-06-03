<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MarketerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $permissions = [
            // ads
            'ad.list',
            'ad.create',
            'ad.read',
            'ad.update',
            'ad.delete',
        ];

        foreach($permissions as $p) {
            $permission = Permission::create(['name' => $p]);
        }


        $role = Role::create(['name' => 'marketer']);
        
        foreach($permissions as $p) {
            Permission::where('name', $p)->firstOrFail()->assignRole($role);
        }

        $marketer = User::create([
            'name' => 'Marketer',
            'email' => 'marketer@gmail.com',
            'password' => bcrypt('marketer123')
        ]);
        $marketer->assignRole('marketer');


    }
}
