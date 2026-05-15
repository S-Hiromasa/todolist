<x-layout title="{{ $project->name }}" heading="{{ $project->name }}" subheading="プロジェクトに紐づくTodoと進捗を確認できます。">
    <x-slot:action>
        @if ($canWrite)
            <a class="button" href="{{ route('todos.create', ['project_id' => $project->id]) }}">Todo追加</a>
        @endif
    </x-slot:action>

    <section class="project-summary stack">
        <div>
            <p class="todo-meta">{{ $project->team ? 'チーム: '.$project->team->name : '個人プロジェクト' }}</p>
            <p class="todo-meta">状態: {{ $project->status }}</p>
            @if ($project->deadline)
                <p class="todo-meta">期限: {{ $project->deadline->format('Y/m/d') }}</p>
            @endif
        </div>
        <div class="progress-block">
            <div class="progress-label">
                <span>進捗率</span>
                <strong>{{ $progressRate }}%</strong>
            </div>
            <div class="progress-track">
                <div class="progress-fill" style="width: {{ $progressRate }}%"></div>
            </div>
        </div>
    </section>

    <section class="todo-list">
        @forelse ($todos as $todo)
            <article class="todo-item">
                <div>
                    <h2 class="todo-title {{ $todo->status === 'done' ? 'is-done' : '' }}">
                        {{ $todo->title }}
                        <span class="badge">{{ $todo->status }}</span>
                    </h2>
                    @if ($todo->assignee)
                        <p class="todo-meta">担当: {{ $todo->assignee->name }}</p>
                    @endif
                    @if ($todo->due_date)
                        <p class="todo-meta">期限: {{ $todo->due_date->format('Y/m/d') }}</p>
                    @endif
                    @if ($todo->description)
                        <p class="todo-description">{{ $todo->description }}</p>
                    @endif
                </div>

                @if ($canWrite)
                    <div class="actions">
                        <form class="inline" method="POST" action="{{ route('todos.toggle', $todo) }}">
                            @csrf
                            @method('PATCH')
                            <button class="button secondary" type="submit">
                                {{ $todo->status === 'done' ? '未完了に戻す' : '完了' }}
                            </button>
                        </form>
                        <a class="button secondary" href="{{ route('todos.edit', $todo) }}">編集</a>
                    </div>
                @endif
            </article>
        @empty
            <div class="empty">このプロジェクトのTodoはまだありません。</div>
        @endforelse
    </section>
</x-layout>
