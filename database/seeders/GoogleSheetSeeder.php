<?php

namespace Database\Seeders;

use App\Models\GoogleSheet;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class GoogleSheetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        GoogleSheet::create([
            'name' => 'Google Sheet',
            'sheet_id' => '1Vk5GA5ljV8zaHxVbNdIhw1IuqFtzC9BVrV59Ws4ceS4',
            'sheet_name' => 'Youcan-Orders',
            'is_active' => true,
            'has_errors' => false,
            'created_by' => 1,
        ]);
    }
}
