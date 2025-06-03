<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class FollowupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $permissions = [
            // orders
            'order.list',
            'order.create',
            'order.read',
            'order.update',
            'order.delete',
        ];

        $role = Role::create(['name' => 'followup']);
        
        foreach($permissions as $p) {
            Permission::where('name', $p)->firstOrFail()->assignRole($role);
        }

        $delivery = User::create([
            'name' => 'Followup',
            'email' => 'followup@gmail.com',
            'password' => bcrypt('followup123')
        ]);
        $delivery->assignRole('followup');


    }
}
