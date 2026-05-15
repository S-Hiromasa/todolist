<x-layout title="ToDoを追加" heading="ToDoを追加" subheading="予定名と必要なら期限・メモを入力します。">
    <x-slot:action>
        <a class="button secondary" href="{{ $team ? route('teams.show', $team) : route('todos.index') }}">一覧へ戻る</a>
    </x-slot:action>

    <form class="form-panel" method="POST" action="{{ route('todos.store') }}">
        @csrf
        @if ($project)
            <input name="project_id" type="hidden" value="{{ $project->id }}">
        @endif
        @if ($team)
            <input name="team_id" type="hidden" value="{{ $team->id }}">
        @endif
        @include('todos.partials.form', ['todo' => null])
    </form>
</x-layout>
