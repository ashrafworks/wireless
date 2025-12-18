<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $cities = collect([
            ['country_id' => 234, 'name' => 'New York', 'is_active' => true],
            ['country_id' => 234, 'name' => 'Los Angeles', 'is_active' => true],
            ['country_id' => 234, 'name' => 'Chicago', 'is_active' => true],
            ['country_id' => 234, 'name' => 'Houston', 'is_active' => true],
            ['country_id' => 234, 'name' => 'Phoenix', 'is_active' => true],
            ['country_id' => 234, 'name' => 'Philadelphia', 'is_active' => true],
            ['country_id' => 234, 'name' => 'San Antonio', 'is_active' => true],
            ['country_id' => 234, 'name' => 'San Diego', 'is_active' => true],
            ['country_id' => 234, 'name' => 'Dallas', 'is_active' => true],
            ['country_id' => 234, 'name' => 'San Jose', 'is_active' => true],
            // Add more cities as needed
        ]);

        $cities->each(fn($city) => City::create($city));
    }

}
