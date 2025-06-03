<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('admin123')
        ]);
        $admin->assignRole('admin');

        $agent = User::create([
            'name' => 'Agent Agent',
            'email' => 'agent@gmail.com',
            'password' => bcrypt('agent123')
        ]);
        $agent->assignRole('agent');


        $delivery = User::create([
            'name' => 'Nawris',
            'email' => 'nawris@gmail.com',
            'password' => bcrypt('nawris123')
        ]);
        $delivery->assignRole('delivery');


    }
}
