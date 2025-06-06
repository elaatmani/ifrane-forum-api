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


        $user = User::create([
            'name' => 'Attendant Attendant',
            'email' => 'attendant@gmail.com',
            'password' => bcrypt('attendant123')
        ]);
        $user->assignRole('attendant');

        $user = User::create([
            'name' => 'Exhibitor Exhibitor',
            'email' => 'exhibitor@gmail.com',
            'password' => bcrypt('exhibitor123')
        ]);
        $user->assignRole('exhibitor');

        $user = User::create([
            'name' => 'Buyer Buyer',
            'email' => 'buyer@gmail.com',
            'password' => bcrypt('buyer123')
        ]);
        $user->assignRole('buyer');

        $user = User::create([
            'name' => 'Sponsor Sponsor',
            'email' => 'sponsor@gmail.com',
            'password' => bcrypt('sponsor123')
        ]);
        $user->assignRole('sponsor');

        $user = User::create([
            'name' => 'Speaker Speaker',
            'email' => 'speaker@gmail.com',
            'password' => bcrypt('speaker123')
        ]);
        $user->assignRole('speaker');


    }
}
