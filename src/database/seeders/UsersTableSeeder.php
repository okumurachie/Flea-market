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

        $testUsers = [
            [
                'name' => 'Test User1',
                'email' => 'test1@example.com',
                'password' => 'abcd1234',
            ],
            [
                'name' => 'Test User2',
                'email' => 'test2@example.com',
                'password' => 'abcd5678',
            ],
            [
                'name' => 'Test User3',
                'email' => 'test3@example.com',
                'password' => 'abcd4321',
            ],
        ];

        foreach ($testUsers as $index => $userData) {
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'email_verified_at' => now(),
                'password' => Hash::make($userData['password']),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Profile::create([
                'user_id' => $user->id,
                'user_name' => $userData['name'],
                'post_code' => $faker->numerify('###-####'),
                'address' => $faker->prefecture() . $faker->city() . $faker->streetAddress(),
                'building' => $faker->secondaryAddress(),
                'image' => 'storage/images/ProfilesSeeder/user' . $user->id . '.png',
                'profile_completed' => true,
                'created_at' => now(),
                'updated_at' => now(),

            ]);
        }
    }
}
