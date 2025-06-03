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

            // orders
            'order.list',
            'order.create',
            'order.read',
            'order.update',
            'order.delete',

            // products
            'product.list',
            'product.create',
            'product.read',
            'product.update',
            'product.delete',

            // sheets
            'sheet.list',
            'sheet.create',
            'sheet.read',
            'sheet.update',
            'sheet.delete',
        ];

        $roles = [
            [
                'name' => 'super_admin',
                'permissions' => [

                ]
            ],
            [
                'name' => 'admin',
                'permissions' => $permissions
                ],

                [
                    'name' => 'agent',
                    'permissions' => [
    
                        // orders
                        'order.list',
                        'order.create',
                        'order.read',
                        'order.update',
                        'order.delete',
    
                        // products
                        'product.list',
                        'product.read',
                    ]
                ],
                [
                    'name' => 'delivery',
                    'permissions' => [
    
                        // orders
                        'order.list',
                        'order.read',
                        'order.update',
    
                        // products
                        'product.list',
                        'product.read',
                    ]
                    ],
        ];

        foreach($permissions as $p) {
            $permission = Permission::create(['name' => $p]);
        }

        foreach ($roles as $r) {
            $role = Role::create(['name' => $r['name']]);
            
            foreach($r['permissions'] as $p) {
                $role->givePermissionTo($p);
            }
        }

    }

}
