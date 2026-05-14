<x-layout title="ToDo List" heading="ToDo List" subheading="未完了の予定から順番に確認できます。">
    <x-slot:action>
        <a class="button" href="{{ route('todos.create') }}">追加</a>
    </x-slot:action>

    <section class="todo-list">
        @forelse ($todos as $todo)
            <article class="todo-item">
                <div>
                    <h2 class="todo-title {{ $todo->is_done ? 'is-done' : '' }}">
                        {{ $todo->title }}
                        @if ($todo->is_done)
                            <span class="badge">完了</span>
                        @endif
                    </h2>

                    @if ($todo->due_date)
                        <p class="todo-meta">期限: {{ $todo->due_date->format('Y/m/d') }}</p>
                    @endif

                    @if ($todo->description)
                        <p class="todo-description">{{ $todo->description }}</p>
                    @endif
                </div>

                <div class="actions">
                    <form class="inline" method="POST" action="{{ route('todos.toggle', $todo) }}">
                        @csrf
                        @method('PATCH')
                        <button class="button secondary" type="submit">
                            {{ $todo->is_done ? '未完了に戻す' : '完了' }}
                        </button>
                    </form>
                    <a class="button secondary" href="{{ route('todos.edit', $todo) }}">編集</a>
                    <form class="inline" method="POST" action="{{ route('todos.destroy', $todo) }}">
                        @csrf
                        @method('DELETE')
                        <button class="button danger" type="submit">削除</button>
                    </form>
                </div>
            </article>
        @empty
            <div class="empty">まだ ToDo はありません。最初の予定を追加しましょう。</div>
        @endforelse
    </section>
</x-layout>
