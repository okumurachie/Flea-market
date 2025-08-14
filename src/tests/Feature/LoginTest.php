<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class LoginTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_login_fails_when_email_is_missing()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);

        $response = $this->from('/login')->post('/login', [
            'email' => '',
            'password' => 'abcd1234',
        ]);
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください'
        ]);
        $response = $this->get('/login');
        $response->assertSee('メールアドレスを入力してください');
    }

    public function test_login_fails_when_password_is_missing()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);

        $response = $this->from('/login')->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);

        $response = $this->get('/login');
        $response->assertSee('パスワードを入力してください');
    }

    public function test_login_fails_when_input_information_is_incorrect()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);

        $response = $this->from('/login')->post('/login', [
            'email' => 'test1@example.com',
            'password' => 'abcc1234',
        ]);
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);

        $response = $this->get('/login');
        $response->assertSee('ログイン情報が登録されていません');
    }

    public function test_login_user_without_profile_redirects_to_profile_setting_page()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/mypage/profile');

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_user_with_profile_redirects_to_homepage()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);

        $user->profile()->create([
            'user_name' => 'Test User',
            'post_code' => '123-4567',
            'address' => '東京都渋谷区1-1-1',
            'image' => 'test.jpg',
            'profile_completed' => true,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
    }
}
