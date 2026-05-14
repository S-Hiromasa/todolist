<x-layout title="チーム" heading="チーム" subheading="所属しているチームのTodoを共有できます。">
    <x-slot:action>
        <a class="button" href="{{ route('teams.create') }}">チーム作成</a>
    </x-slot:action>

    <section class="todo-list">
        @forelse ($teams as $team)
            <article class="todo-item">
                <div>
                    <h2 class="todo-title">{{ $team->name }}</h2>
                    <p class="todo-meta">権限: {{ $team->pivot->role }}</p>
                </div>
                <div class="actions">
                    <a class="button secondary" href="{{ route('teams.show', $team) }}">開く</a>
                </div>
            </article>
        @empty
            <div class="empty">まだチームに所属していません。最初のチームを作成しましょう。</div>
        @endforelse
    </section>
</x-layout>
