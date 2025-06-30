<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'post_code' => $this->faker->numerify('###-####'),
            'address' => $this->faker->address(),
            'building' => $this->faker->secondaryAddress(),
            'image' => function (array $attributes) {
                $user = User::find($attributes['user_id']);
                $name = $user ? $user->name : 'No Name';
                return 'https://placehold.jp/640x480.png?text=' . urlencode($name);
            },
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
