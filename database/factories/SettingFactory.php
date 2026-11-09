<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Setting>
 */
class SettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $word = $this->faker->unique()->word . '_' . $this->faker->randomNumber(4);
        return [
            'key' => $word,
            'name' => $word,
            'value' => $this->faker->sentence,
            'default_value' => null,
            'is_encrypted' => false,
            'description' => $this->faker->text,
        ];
    }
}
