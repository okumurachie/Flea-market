<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use App\Models\User;


class RegisterTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */

    public function test_register_fails_when_name_is_missing()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);

        $response = $this->from('/register')->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'abcd1234',
            'password_confirmation' => 'abcd1234',
        ]);
        $response->assertRedirect('/register');
        $response->assertSessionHasErrors([
            'name' => 'お名前を入力してください'
        ]);
        $response = $this->get('/register');
        $response->assertSee('お名前を入力してください');
    }

    public function test_register_fails_when_email_is_missing()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);

        $response = $this->from('/register')->post('/register', [
            'name' => 'Test User',
            'email' => '',
            'password' => 'abcd1234',
            'password_confirmation' => 'abcd1234',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください'
        ]);
        $response = $this->get('/register');
        $response->assertSee('メールアドレスを入力してください');
    }

    public function test_register_fails_when_password_is_missing()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);

        $response = $this->from('/register')->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください'
        ]);
        $response = $this->get('/register');
        $response->assertSee('パスワードを入力してください');
    }

    public function test_register_fails_when_password_is_too_short()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);

        $response = $this->from('/register')->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'abcd123',
            'password_confirmation' => 'abcd123',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください'
        ]);

        $response = $this->get('/register');
        $response->assertSee('パスワードは8文字以上で入力してください');
    }

    public function test_register_fails_when_password_confirmation_does_not_match()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);

        $response = $this->from('/register')->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'abcc1234',
            'password_confirmation' => 'abcd1234',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors([
            'password' => 'パスワードと一致しません'
        ]);

        $response = $this->get('/register');
        $response->assertSee('パスワードと一致しません');
    }
}
