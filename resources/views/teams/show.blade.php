<x-layout title="{{ $team->name }}" heading="{{ $team->name }}" subheading="このチームで共有しているTodoです。">
    <x-slot:action>
        @if ($role !== 'viewer')
            <a class="button" href="{{ route('todos.create', ['team_id' => $team->id]) }}">Todo追加</a>
        @endif
    </x-slot:action>

    @if ($role === 'admin')
        <form class="form-panel stack" method="POST" action="{{ route('teams.invite', $team) }}">
            @csrf
            <div class="field">
                <label for="email">招待するユーザーのメールアドレス</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required>
                @error('email')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="field">
                <label for="role">権限</label>
                <select id="role" name="role" required>
                    <option value="member" @selected(old('role') === 'member')>メンバー</option>
                    <option value="viewer" @selected(old('role') === 'viewer')>閲覧のみ</option>
                    <option value="admin" @selected(old('role') === 'admin')>管理者</option>
                </select>
                @error('role')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit">メンバー追加</button>
            </div>
        </form>
    @endif

    <section class="table-panel stack">
        <table class="data-table">
            <thead>
                <tr>
                    <th>メンバー</th>
                    <th>メールアドレス</th>
                    <th>権限</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($team->users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td><span class="badge">{{ $user->pivot->role }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>

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

                @if ($role !== 'viewer')
                    <div class="actions">
                        <form class="inline" method="POST" action="{{ route('todos.toggle', $todo) }}">
                            @csrf
                            @method('PATCH')
                            <button class="button secondary" type="submit">
                                {{ $todo->is_done ? '未完了に戻す' : '完了' }}
                            </button>
                        </form>
                        <a class="button secondary" href="{{ route('todos.edit', $todo) }}">編集</a>
                        @if ($role === 'admin')
                            <form class="inline" method="POST" action="{{ route('todos.destroy', $todo) }}">
                                @csrf
                                @method('DELETE')
                                <button class="button danger" type="submit">削除</button>
                            </form>
                        @endif
                    </div>
                @endif
            </article>
        @empty
            <div class="empty">このチームのTodoはまだありません。</div>
        @endforelse
    </section>
</x-layout>
