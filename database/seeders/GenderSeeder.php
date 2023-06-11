<?php

namespace Database\Seeders;

use App\Models\Gender;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GenderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        \App\Models\Gender::create([
            'gender' => 'Male'
        ]);
        \App\Models\Gender::create([
            'gender' => 'Female'
        ]);
        \App\Models\Gender::create([
            'gender' => 'I prefer not to say'
        ]);
    }
}
