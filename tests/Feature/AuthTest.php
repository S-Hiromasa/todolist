<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Taro',
            'email' => 'taro@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('todos.index'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'Taro',
            'email' => 'taro@example.com',
            'is_admin' => false,
        ]);
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::create([
            'name' => 'Taro',
            'email' => 'taro@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertRedirect(route('todos.index'));

        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
