<x-layout title="ToDoを追加" heading="ToDoを追加" subheading="予定名と必要なら期限・メモを入力します。">
    <x-slot:action>
        <a class="button secondary" href="{{ route('todos.index') }}">一覧へ戻る</a>
    </x-slot:action>

    <form class="form-panel" method="POST" action="{{ route('todos.store') }}">
        @csrf
        @include('todos.partials.form', ['todo' => null])
    </form>
</x-layout>
