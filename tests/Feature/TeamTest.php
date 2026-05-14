<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_team(): void
    {
        $user = $this->user('owner@example.com');
        $this->actingAs($user);

        $this->post(route('teams.store'), ['name' => '開発チーム'])
            ->assertRedirect();

        $team = Team::where('name', '開発チーム')->firstOrFail();

        $this->assertSame($user->id, $team->owner_id);
        $this->assertSame(Team::ROLE_ADMIN, $team->users()->where('users.id', $user->id)->first()->pivot->role);
    }

    public function test_admin_can_invite_existing_user_with_role(): void
    {
        [$team, $admin] = $this->teamWithUser('admin@example.com', Team::ROLE_ADMIN);
        $member = $this->user('member@example.com');

        $this->actingAs($admin);

        $this->post(route('teams.invite', $team), [
            'email' => $member->email,
            'role' => Team::ROLE_VIEWER,
        ])->assertRedirect(route('teams.show', $team));

        $this->assertSame(Team::ROLE_VIEWER, $team->users()->where('users.id', $member->id)->first()->pivot->role);
    }

    public function test_viewer_can_view_team_but_cannot_create_todo(): void
    {
        [$team, $viewer] = $this->teamWithUser('viewer@example.com', Team::ROLE_VIEWER);
        Todo::create(['title' => '共有Todo', 'user_id' => $viewer->id, 'team_id' => $team->id]);

        $this->actingAs($viewer);

        $this->get(route('teams.show', $team))
            ->assertOk()
            ->assertSee('共有Todo');

        $this->post(route('todos.store'), [
            'title' => '作れないTodo',
            'team_id' => $team->id,
        ])->assertForbidden();
    }

    public function test_member_can_create_team_todo_but_cannot_delete_it(): void
    {
        [$team, $member] = $this->teamWithUser('member@example.com', Team::ROLE_MEMBER);

        $this->actingAs($member);

        $this->post(route('todos.store'), [
            'title' => '共有Todo',
            'team_id' => $team->id,
        ])->assertRedirect(route('teams.show', $team));

        $todo = Todo::where('title', '共有Todo')->firstOrFail();
        $this->assertSame($team->id, $todo->team_id);

        $this->delete(route('todos.destroy', $todo))->assertForbidden();
    }

    public function test_non_member_cannot_view_team(): void
    {
        [$team] = $this->teamWithUser('admin@example.com', Team::ROLE_ADMIN);
        $user = $this->user('outsider@example.com');

        $this->actingAs($user);

        $this->get(route('teams.show', $team))->assertForbidden();
    }

    private function teamWithUser(string $email, string $role): array
    {
        $user = $this->user($email);
        $team = Team::create([
            'name' => 'Example Team',
            'owner_id' => $user->id,
        ]);
        $team->users()->attach($user->id, ['role' => $role]);

        return [$team, $user];
    }

    private function user(string $email): User
    {
        return User::create([
            'name' => 'Test User',
            'email' => $email,
            'password' => Hash::make('password123'),
        ]);
    }
}
