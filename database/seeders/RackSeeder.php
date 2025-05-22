<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rack;

class RackSeeder extends Seeder
{
    public function run(): void
    {
        Rack::factory()
            ->count(5)
            ->create();
    }
}
