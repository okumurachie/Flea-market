<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create('ja_JP');
        $testUser = User::create([
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('abcd1234'),
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Profile::create([
            'user_id' => $testUser->id,
            'user_name' => 'Test User',
            'post_code' => $faker->numerify('###-####'),
            'address' => $faker->address(),
            'building' => $faker->secondaryAddress(),
            'image' => 'storage/images/ProfilesSeeder/user1.png',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        User::factory()
            ->count(4)
            ->create()
            ->each(function ($user) use ($faker) {
                Profile::create([
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'post_code' => $faker->numerify('###-####'),
                    'address' => $faker->address,
                    'building' => $faker->secondaryAddress,
                    'image' => 'storage/images/ProfilesSeeder/user' . $user->id . '.png',
                ]);
            });
    }
}
