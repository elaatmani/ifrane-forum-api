<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $permissions = [
            // users
            'user.list',
            'user.create',
            'user.read',
            'user.update',
            'user.delete',

            // products
            'product.list',
            'product.create',
            'product.read',
            'product.update',
            'product.delete',

        ];

        $roles = [
            [
                'name' => 'admin',
                'permissions' => $permissions
            ],

            [
                'name' => 'attendant',
                'permissions' => []
            ]
            ,
            [
                'name' => 'exhibitor',
                'permissions' => []
            ],
            [
                'name' => 'buyer',
                'permissions' => []
            ],

            [
                'name' => 'sponsor',
                'permissions' => []
            ],

            [
                'name' => 'speaker',
                'permissions' => []
            ]

        ];

        foreach ($permissions as $p) {
            $permission = Permission::create(['name' => $p]);
        }

        foreach ($roles as $r) {
            $role = Role::create(['name' => $r['name']]);

            foreach ($r['permissions'] as $p) {
                $role->givePermissionTo($p);
            }
        }

    }

}
