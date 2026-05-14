<x-layout title="ログイン" heading="ログイン" subheading="登録済みのユーザーでToDoを開きます。">
    <x-slot:action>
        <a class="button secondary" href="{{ route('register') }}">登録へ</a>
    </x-slot:action>

    <form class="form-panel" method="POST" action="{{ route('login') }}">
        @csrf

        <div class="field">
            <label for="email">メールアドレス</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
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

        <label class="checkbox">
            <input name="remember" type="checkbox" value="1">
            ログイン状態を保持する
        </label>

        <div class="form-actions">
            <a class="button secondary" href="{{ route('register') }}">登録へ</a>
            <button type="submit">ログイン</button>
        </div>
    </form>
</x-layout>
