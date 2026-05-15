<x-layout title="ToDoを編集" heading="ToDoを編集" subheading="内容や完了状態を更新できます。">
    <x-slot:action>
        <a class="button secondary" href="{{ $todo->team_id ? route('teams.show', $todo->team_id) : route('todos.index') }}">一覧へ戻る</a>
    </x-slot:action>

    <form class="form-panel" method="POST" action="{{ route('todos.update', $todo) }}">
        @csrf
        @method('PUT')
        @include('todos.partials.form', ['todo' => $todo])
    </form>
</x-layout>
