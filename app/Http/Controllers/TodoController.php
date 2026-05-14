<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TodoController extends Controller
{
    public function index(): View
    {
        $todos = auth()->user()->todos()
            ->orderBy('is_done')
            ->orderByRaw('due_date is null')
            ->orderBy('due_date')
            ->latest()
            ->get();

        return view('todos.index', ['todos' => $todos]);
    }

    public function create(): View
    {
        return view('todos.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Todo::create([
            ...$this->validated($request),
            'user_id' => $request->user()->id,
        ]);

        return redirect()->route('todos.index')->with('status', 'ToDoを追加しました。');
    }

    public function edit(Todo $todo): View
    {
        $this->ensureOwner($todo);

        return view('todos.edit', ['todo' => $todo]);
    }

    public function update(Request $request, Todo $todo): RedirectResponse
    {
        $this->ensureOwner($todo);

        $todo->update($this->validated($request));

        return redirect()->route('todos.index')->with('status', 'ToDoを更新しました。');
    }

    public function destroy(Todo $todo): RedirectResponse
    {
        $this->ensureOwner($todo);

        $todo->delete();

        return redirect()->route('todos.index')->with('status', 'ToDoを削除しました。');
    }

    public function toggle(Todo $todo): RedirectResponse
    {
        $this->ensureOwner($todo);

        $todo->update(['is_done' => ! $todo->is_done]);

        return redirect()->route('todos.index');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_done' => ['sometimes', 'boolean'],
            'due_date' => ['nullable', 'date'],
        ]);

        $data['is_done'] = $request->boolean('is_done');

        return $data;
    }

    private function ensureOwner(Todo $todo): void
    {
        abort_unless($todo->user_id === auth()->id(), 403);
    }
}
