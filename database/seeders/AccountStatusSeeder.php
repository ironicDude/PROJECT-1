<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AccountStatus;

class AccountStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AccountStatus::create([
            'status' => 'Active'
        ]);
        AccountStatus::create([
            'status' => 'Blocked'
        ]);
    }
}
