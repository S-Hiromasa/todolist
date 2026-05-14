<x-layout title="ユーザー登録" heading="ユーザー登録" subheading="ToDoを自分だけの一覧として管理します。">
    <x-slot:action>
        <a class="button secondary" href="{{ route('login') }}">ログインへ</a>
    </x-slot:action>

    <form class="form-panel" method="POST" action="{{ route('register') }}">
        @csrf

        <div class="field">
            <label for="name">名前</label>
            <input id="name" name="name" value="{{ old('name') }}" required autofocus>
            @error('name')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="field">
            <label for="email">メールアドレス</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required>
            @error('email')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="field">
            <label for="password">パスワード</label>
            <input id="password" name="password" type="password" required>
            @error('password')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="field">
            <label for="password_confirmation">パスワード確認</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required>
        </div>

        <div class="form-actions">
            <a class="button secondary" href="{{ route('login') }}">ログインへ</a>
            <button type="submit">登録</button>
        </div>
    </form>
</x-layout>
