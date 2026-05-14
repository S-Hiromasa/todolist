<?php

namespace Tests\Feature;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TodoTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_todo(): void
    {
        $user = $this->user();

        $response = $this->post(route('todos.store'), [
            'title' => '買い物',
            'description' => '牛乳を買う',
            'due_date' => '2026-05-20',
        ]);

        $response->assertRedirect(route('todos.index'));
        $this->assertDatabaseHas('todos', [
            'title' => '買い物',
            'description' => '牛乳を買う',
            'is_done' => false,
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_toggle_todo(): void
    {
        $user = $this->user();
        $todo = Todo::create(['title' => '掃除', 'user_id' => $user->id]);

        $this->patch(route('todos.toggle', $todo))->assertRedirect(route('todos.index'));

        $this->assertTrue($todo->fresh()->is_done);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('todos.index'))->assertRedirect(route('login'));
    }

    public function test_user_cannot_see_another_users_todo(): void
    {
        $user = $this->user('user@example.com');
        $other = User::create([
            'name' => 'Other',
            'email' => 'other@example.com',
            'password' => Hash::make('password'),
        ]);

        Todo::create(['title' => '自分のToDo', 'user_id' => $user->id]);
        Todo::create(['title' => '他人のToDo', 'user_id' => $other->id]);

        $this->get(route('todos.index'))
            ->assertOk()
            ->assertSee('自分のToDo')
            ->assertDontSee('他人のToDo');
    }

    public function test_user_cannot_update_another_users_todo(): void
    {
        $this->user('user@example.com');
        $other = User::create([
            'name' => 'Other',
            'email' => 'other@example.com',
            'password' => Hash::make('password'),
        ]);
        $todo = Todo::create(['title' => '他人のToDo', 'user_id' => $other->id]);

        $this->put(route('todos.update', $todo), ['title' => '変更'])->assertForbidden();
        $this->delete(route('todos.destroy', $todo))->assertForbidden();
        $this->patch(route('todos.toggle', $todo))->assertForbidden();
    }

    private function user(string $email = 'test@example.com'): User
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => $email,
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user);

        return $user;
    }
}
