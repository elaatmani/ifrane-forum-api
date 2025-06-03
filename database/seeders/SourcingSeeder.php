<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SourcingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $permissions = [
            // ads
            'sourcing.list',
            'sourcing.create',
            'sourcing.read',
            'sourcing.update',
            'sourcing.delete',
        ];

        foreach($permissions as $p) {
            $permission = Permission::create(['name' => $p]);
        }


        $roles = Role::whereIn('name', ['admin'])->get();
        
        foreach($roles as $role) {
            foreach($permissions as $p) {
                Permission::where('name', $p)->firstOrFail()->assignRole($role);
            }
        }

    }
}
