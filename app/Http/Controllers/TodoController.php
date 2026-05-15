<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Team;
use App\Models\Todo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TodoController extends Controller
{
    public function index(): View
    {
        $todos = auth()->user()->todos()
            ->whereNull('team_id')
            ->whereNull('project_id')
            ->orderBy('is_done')
            ->orderByRaw('due_date is null')
            ->orderBy('due_date')
            ->latest()
            ->get();

        return view('todos.index', ['todos' => $todos]);
    }

    public function create(Request $request): View
    {
        $team = null;
        $project = null;

        if ($request->filled('project_id')) {
            $project = Project::findOrFail($request->integer('project_id'));
            $this->ensureCanWriteProjectTodo($request, $project);
            $team = $project->team;
        }

        if (! $project && $request->filled('team_id')) {
            $team = Team::findOrFail($request->integer('team_id'));
            $this->ensureCanWriteTeamTodo($request, $team);
        }

        return view('todos.create', [
            'team' => $team,
            'project' => $project,
            'assignees' => $this->assigneesFor($request, $team, $project),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $team = null;
        $project = null;

        if ($request->filled('project_id')) {
            $project = Project::findOrFail($request->integer('project_id'));
            $this->ensureCanWriteProjectTodo($request, $project);
            $team = $project->team;
        }

        if (! $project && $request->filled('team_id')) {
            $team = Team::findOrFail($request->integer('team_id'));
            $this->ensureCanWriteTeamTodo($request, $team);
        }

        $data = $this->validated($request);
        $this->ensureValidAssignee($request, $team, $project, $data['assignee_id'] ?? null);

        Todo::create([
            ...$data,
            'user_id' => $request->user()->id,
            'team_id' => $team?->id,
            'project_id' => $project?->id,
        ]);

        $redirect = $this->redirectFor($project, $team);

        return redirect($redirect)->with('status', 'ToDoを追加しました。');
    }

    public function edit(Request $request, Todo $todo): View
    {
        $this->ensureCanWriteTodo($request, $todo);

        return view('todos.edit', [
            'todo' => $todo,
            'assignees' => $this->assigneesFor($request, $todo->team, $todo->project),
        ]);
    }

    public function update(Request $request, Todo $todo): RedirectResponse
    {
        $this->ensureCanWriteTodo($request, $todo);

        $data = $this->validated($request);
        $this->ensureValidAssignee($request, $todo->team, $todo->project, $data['assignee_id'] ?? null);

        $todo->update($data);

        $redirect = $this->redirectFor($todo->project, $todo->team);

        return redirect($redirect)->with('status', 'ToDoを更新しました。');
    }

    public function destroy(Request $request, Todo $todo): RedirectResponse
    {
        $this->ensureCanDeleteTodo($request, $todo);
        $teamId = $todo->team_id;
        $projectId = $todo->project_id;

        $todo->delete();

        $redirect = $projectId
            ? route('projects.show', $projectId)
            : ($teamId ? route('teams.show', $teamId) : route('todos.index'));

        return redirect($redirect)->with('status', 'ToDoを削除しました。');
    }

    public function toggle(Request $request, Todo $todo): RedirectResponse
    {
        $this->ensureCanWriteTodo($request, $todo);

        $isDone = ! $todo->is_done;

        $todo->update([
            'is_done' => $isDone,
            'status' => $isDone ? Todo::STATUS_DONE : Todo::STATUS_TODO,
        ]);

        $redirect = $this->redirectFor($todo->project, $todo->team);

        return redirect($redirect);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_done' => ['sometimes', 'boolean'],
            'status' => ['nullable', 'in:todo,doing,done'],
            'due_date' => ['nullable', 'date'],
            'team_id' => ['nullable', 'integer', 'exists:teams,id'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'assignee_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $data['status'] = $data['status'] ?? ($request->boolean('is_done') ? Todo::STATUS_DONE : Todo::STATUS_TODO);
        $data['is_done'] = $data['status'] === Todo::STATUS_DONE;
        unset($data['team_id'], $data['project_id']);

        return $data;
    }

    private function ensureCanWriteTodo(Request $request, Todo $todo): void
    {
        if ($todo->project_id) {
            $this->ensureCanWriteProjectTodo($request, $todo->project);

            return;
        }

        if ($todo->team_id) {
            $this->ensureCanWriteTeamTodo($request, $todo->team);

            return;
        }

        abort_unless($todo->user_id === $request->user()->id, 403);
    }

    private function ensureCanDeleteTodo(Request $request, Todo $todo): void
    {
        if ($todo->project_id) {
            $project = $todo->project;

            if ($project->team_id) {
                abort_unless($this->teamRole($request, $project->team) === Team::ROLE_ADMIN, 403);

                return;
            }

            abort_unless($project->owner_id === $request->user()->id, 403);

            return;
        }

        if ($todo->team_id) {
            abort_unless($this->teamRole($request, $todo->team) === Team::ROLE_ADMIN, 403);

            return;
        }

        abort_unless($todo->user_id === $request->user()->id, 403);
    }

    private function ensureCanWriteTeamTodo(Request $request, Team $team): void
    {
        abort_unless(in_array($this->teamRole($request, $team), [Team::ROLE_ADMIN, Team::ROLE_MEMBER], true), 403);
    }

    private function ensureCanWriteProjectTodo(Request $request, Project $project): void
    {
        if ($project->team_id) {
            $this->ensureCanWriteTeamTodo($request, $project->team);

            return;
        }

        abort_unless($project->owner_id === $request->user()->id, 403);
    }

    private function ensureValidAssignee(Request $request, ?Team $team, ?Project $project, ?int $assigneeId): void
    {
        if (! $assigneeId) {
            return;
        }

        if ($team) {
            abort_unless($team->users()->where('users.id', $assigneeId)->exists(), 422);

            return;
        }

        if ($project && ! $project->team_id) {
            abort_unless($assigneeId === $request->user()->id, 422);

            return;
        }

        abort_unless($assigneeId === $request->user()->id, 422);
    }

    private function assigneesFor(Request $request, ?Team $team, ?Project $project)
    {
        if ($team) {
            return $team->users()
                ->orderBy('name')
                ->get();
        }

        if ($project && ! $project->team_id) {
            return collect([$request->user()]);
        }

        return collect([$request->user()]);
    }

    private function redirectFor(?Project $project, ?Team $team): string
    {
        if ($project) {
            return route('projects.show', $project);
        }

        if ($team) {
            return route('teams.show', $team);
        }

        return route('todos.index');
    }

    private function teamRole(Request $request, Team $team): ?string
    {
        $membership = $request->user()->teams()
            ->where('teams.id', $team->id)
            ->first();

        return $membership?->pivot->role;
    }
}
