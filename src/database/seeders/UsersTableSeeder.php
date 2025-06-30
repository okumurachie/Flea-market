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

        $profileData = Profile::factory()->make([
            'user_id' => $testUser->id,
        ])->attributesToArray();

        Profile::create($profileData);

        User::factory()
            ->count(4)
            ->has(Profile::factory())
            ->create();
    }
}
