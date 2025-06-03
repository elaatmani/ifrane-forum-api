<?php

namespace Database\Seeders;

use App\Services\NawrisService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = NawrisService::cities()['feed'];


        foreach ($cities as $c) {
            $city = \App\Models\City::create([
                'nawris_city_id' => $c['id'],
                'name' => $c['name'],
            ]);

            $areas = NawrisService::areas($c['id'])['feed'];

            foreach ($areas as $a) {
                \App\Models\CityArea::create([
                    'city_id' => $city->id,
                    'nawris_area_id' => $a['id'],
                    'name' => $a['name'],
                ]);
            }
        }


    }
}
