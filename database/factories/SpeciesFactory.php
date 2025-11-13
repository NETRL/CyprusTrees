<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Species>
 */
class SpeciesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $latin = fake()->unique()->words(2, true); // e.g. "Quercus robur"
        $common = ucfirst(fake()->unique()->word()); // e.g. "Oak"

        return [
            'latin_name'        => $latin,
            'common_name'       => $common,
            'family'            => fake()->randomElement(['Fagaceae', 'Pinaceae', 'Rosaceae', 'Fabaceae', 'Cupressaceae']),
            'drought_tolerance' => fake()->randomElement(['Low', 'Moderate', 'High']),
            'canopy_class'      => fake()->randomElement(['S', 'M', 'L']), // Small/Medium/Large canopy
            'notes'             => fake()->sentence(),
        ];
    }
}
