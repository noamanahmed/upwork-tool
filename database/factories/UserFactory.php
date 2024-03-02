<?php

namespace Database\Factories;

use App\Enums\UserStatusEnum;
use App\Enums\UserTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = $this->faker;
        $country = $faker->country();
        $state = $faker->state();
        $city = $faker->city();
        $address = $faker->address();
        $phoneNumber = str_replace('.', '', $this->faker->phoneNumber($country));
        $type = $this->faker->randomElement(UserTypeEnum::toArray());
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'phone' => $phoneNumber,
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'country' => $country,
            'status' => $this->faker->randomElement(UserStatusEnum::toArray()),
            'type' => $type,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
