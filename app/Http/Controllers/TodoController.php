<?php

namespace App\Http\Controllers;

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

        if ($request->filled('team_id')) {
            $team = Team::findOrFail($request->integer('team_id'));
            $this->ensureCanWriteTeamTodo($request, $team);
        }

        return view('todos.create', ['team' => $team]);
    }

    public function store(Request $request): RedirectResponse
    {
        $team = null;

        if ($request->filled('team_id')) {
            $team = Team::findOrFail($request->integer('team_id'));
            $this->ensureCanWriteTeamTodo($request, $team);
        }

        Todo::create([
            ...$this->validated($request),
            'user_id' => $request->user()->id,
            'team_id' => $team?->id,
        ]);

        $redirect = $team ? route('teams.show', $team) : route('todos.index');

        return redirect($redirect)->with('status', 'ToDoを追加しました。');
    }

    public function edit(Request $request, Todo $todo): View
    {
        $this->ensureCanWriteTodo($request, $todo);

        return view('todos.edit', ['todo' => $todo]);
    }

    public function update(Request $request, Todo $todo): RedirectResponse
    {
        $this->ensureCanWriteTodo($request, $todo);

        $todo->update($this->validated($request));

        $redirect = $todo->team_id ? route('teams.show', $todo->team_id) : route('todos.index');

        return redirect($redirect)->with('status', 'ToDoを更新しました。');
    }

    public function destroy(Request $request, Todo $todo): RedirectResponse
    {
        $this->ensureCanDeleteTodo($request, $todo);
        $teamId = $todo->team_id;

        $todo->delete();

        $redirect = $teamId ? route('teams.show', $teamId) : route('todos.index');

        return redirect($redirect)->with('status', 'ToDoを削除しました。');
    }

    public function toggle(Request $request, Todo $todo): RedirectResponse
    {
        $this->ensureCanWriteTodo($request, $todo);

        $todo->update(['is_done' => ! $todo->is_done]);

        $redirect = $todo->team_id ? route('teams.show', $todo->team_id) : route('todos.index');

        return redirect($redirect);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_done' => ['sometimes', 'boolean'],
            'due_date' => ['nullable', 'date'],
            'team_id' => ['nullable', 'integer', 'exists:teams,id'],
        ]);

        $data['is_done'] = $request->boolean('is_done');
        unset($data['team_id']);

        return $data;
    }

    private function ensureCanWriteTodo(Request $request, Todo $todo): void
    {
        if ($todo->team_id) {
            $this->ensureCanWriteTeamTodo($request, $todo->team);

            return;
        }

        abort_unless($todo->user_id === $request->user()->id, 403);
    }

    private function ensureCanDeleteTodo(Request $request, Todo $todo): void
    {
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

    private function teamRole(Request $request, Team $team): ?string
    {
        $membership = $request->user()->teams()
            ->where('teams.id', $team->id)
            ->first();

        return $membership?->pivot->role;
    }
}
