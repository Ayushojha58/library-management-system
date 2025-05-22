<?php

namespace Database\Factories;

use App\Models\Rack;
use Illuminate\Database\Eloquent\Factories\Factory;

class RackFactory extends Factory
{
    protected $model = Rack::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'location' => $this->faker->address,
            'capacity' => $this->faker->numberBetween(50, 200),
        ];
    }
}
