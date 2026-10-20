<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(AcademySeeder::class);
        $this->call(WorkshopSeeder::class);
        $this->call(ReferencePortfolioSeeder::class);
    }
}
