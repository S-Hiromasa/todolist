<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.users.index'))->assertRedirect(route('login'));
    }

    public function test_normal_user_cannot_view_admin_users_page(): void
    {
        $this->actingAs($this->user());

        $this->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_admin_can_view_users_page(): void
    {
        $admin = $this->user('admin@example.com', true);
        $user = $this->user('user@example.com');

        $this->actingAs($admin);

        $this->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee($admin->name)
            ->assertSee($admin->email)
            ->assertSee($user->name)
            ->assertSee($user->email)
            ->assertSee('管理者')
            ->assertSee('一般ユーザー');
    }

    private function user(string $email = 'user@example.com', bool $isAdmin = false): User
    {
        return User::create([
            'name' => $isAdmin ? 'Admin User' : 'Normal User',
            'email' => $email,
            'password' => Hash::make('password123'),
            'is_admin' => $isAdmin,
        ]);
    }
}
