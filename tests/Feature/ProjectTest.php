<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Team;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('projects.index'))->assertRedirect(route('login'));
    }

    public function test_user_can_create_personal_project(): void
    {
        $user = $this->user('owner@example.com');
        $this->actingAs($user);

        $this->post(route('projects.store'), [
            'name' => '個人プロジェクト',
            'status' => Project::STATUS_ACTIVE,
            'deadline' => '2026-06-01',
        ])->assertRedirect();

        $this->assertDatabaseHas('projects', [
            'name' => '個人プロジェクト',
            'owner_id' => $user->id,
            'team_id' => null,
            'status' => Project::STATUS_ACTIVE,
        ]);
    }

    public function test_member_can_create_team_project(): void
    {
        [$team, $member] = $this->teamWithUser('member@example.com', Team::ROLE_MEMBER);
        $this->actingAs($member);

        $this->post(route('projects.store'), [
            'name' => 'チームプロジェクト',
            'team_id' => $team->id,
            'status' => Project::STATUS_ACTIVE,
        ])->assertRedirect();

        $this->assertDatabaseHas('projects', [
            'name' => 'チームプロジェクト',
            'team_id' => $team->id,
            'owner_id' => $member->id,
        ]);
    }

    public function test_viewer_cannot_create_team_project(): void
    {
        [$team, $viewer] = $this->teamWithUser('viewer@example.com', Team::ROLE_VIEWER);
        $this->actingAs($viewer);

        $this->post(route('projects.store'), [
            'name' => '作れないプロジェクト',
            'team_id' => $team->id,
            'status' => Project::STATUS_ACTIVE,
        ])->assertForbidden();
    }

    public function test_non_member_cannot_view_team_project(): void
    {
        [$team] = $this->teamWithUser('admin@example.com', Team::ROLE_ADMIN);
        $project = Project::create([
            'name' => '見られないプロジェクト',
            'team_id' => $team->id,
            'owner_id' => $team->owner_id,
            'status' => Project::STATUS_ACTIVE,
        ]);
        $outsider = $this->user('outsider@example.com');

        $this->actingAs($outsider);

        $this->get(route('projects.show', $project))->assertForbidden();
    }

    public function test_project_progress_uses_done_todos(): void
    {
        $user = $this->user('owner@example.com');
        $project = Project::create([
            'name' => '進捗プロジェクト',
            'owner_id' => $user->id,
            'status' => Project::STATUS_ACTIVE,
        ]);

        Todo::create([
            'title' => '完了Todo',
            'user_id' => $user->id,
            'project_id' => $project->id,
            'status' => Todo::STATUS_DONE,
            'is_done' => true,
        ]);
        Todo::create([
            'title' => '未着手Todo',
            'user_id' => $user->id,
            'project_id' => $project->id,
            'status' => Todo::STATUS_TODO,
            'is_done' => false,
        ]);

        $this->actingAs($user);

        $this->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee('50%');
    }

    public function test_team_project_todo_can_have_team_member_assignee(): void
    {
        [$team, $member] = $this->teamWithUser('member@example.com', Team::ROLE_MEMBER);
        $project = Project::create([
            'name' => '担当者プロジェクト',
            'team_id' => $team->id,
            'owner_id' => $member->id,
            'status' => Project::STATUS_ACTIVE,
        ]);

        $this->actingAs($member);

        $this->post(route('todos.store'), [
            'title' => '担当つきTodo',
            'project_id' => $project->id,
            'assignee_id' => $member->id,
            'status' => Todo::STATUS_DOING,
            'due_date' => '2026-06-10',
        ])->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('todos', [
            'title' => '担当つきTodo',
            'project_id' => $project->id,
            'team_id' => $team->id,
            'assignee_id' => $member->id,
            'status' => Todo::STATUS_DOING,
            'is_done' => false,
        ]);
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
