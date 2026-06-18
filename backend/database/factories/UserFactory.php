<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'name_ar' => $this->faker->optional()->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->optional()->phoneNumber(),
            'password' => bcrypt('password'),
            'status' => 'active',
            'locale' => 'ar',
            'email_verified_at' => now(),
        ];
    }
}
