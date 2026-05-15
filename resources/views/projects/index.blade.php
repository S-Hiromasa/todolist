<x-layout title="プロジェクト" heading="プロジェクト" subheading="個人とチームの作業をプロジェクト単位で管理します。">
    <x-slot:action>
        <a class="button" href="{{ route('projects.create') }}">プロジェクト作成</a>
    </x-slot:action>

    <section class="todo-list">
        @forelse ($projects as $project)
            <article class="todo-item">
                <div>
                    <h2 class="todo-title">{{ $project->name }}</h2>
                    <p class="todo-meta">
                        {{ $project->team ? 'チーム: '.$project->team->name : '個人プロジェクト' }}
                        / 状態: {{ $project->status }}
                    </p>
                    @if ($project->deadline)
                        <p class="todo-meta">期限: {{ $project->deadline->format('Y/m/d') }}</p>
                    @endif
                </div>
                <div class="actions">
                    <a class="button secondary" href="{{ route('projects.show', $project) }}">開く</a>
                </div>
            </article>
        @empty
            <div class="empty">まだプロジェクトはありません。最初のプロジェクトを作成しましょう。</div>
        @endforelse
    </section>
</x-layout>
