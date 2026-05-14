<x-layout title="ユーザー管理" heading="ユーザー管理" subheading="登録されているユーザーを確認できます。">
    <x-slot:action>
        <a class="button secondary" href="{{ route('todos.index') }}">ToDoへ戻る</a>
    </x-slot:action>

    <section class="table-panel">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ユーザー名</th>
                    <th>メールアドレス</th>
                    <th>権限</th>
                    <th>登録日時</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="badge {{ $user->is_admin ? 'badge-admin' : '' }}">
                                {{ $user->is_admin ? '管理者' : '一般ユーザー' }}
                            </span>
                        </td>
                        <td>{{ $user->created_at->format('Y/m/d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">ユーザーはまだ登録されていません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
</x-layout>
