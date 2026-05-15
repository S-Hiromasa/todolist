<!doctype html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'ToDo List' }}</title>
        <link rel="stylesheet" href="{{ asset('app.css') }}">
    </head>
    <body>
        <main class="app-shell">
            <nav class="auth-nav">
                <a class="brand" href="{{ route('todos.index') }}">ToDo List</a>
                <div class="auth-links">
                    @auth
                        <a href="{{ route('projects.index') }}">プロジェクト</a>
                        <a href="{{ route('teams.index') }}">チーム</a>
                        @if (auth()->user()->is_admin)
                            <a href="{{ route('admin.users.index') }}">ユーザー管理</a>
                        @endif
                        <span>{{ auth()->user()->name }}</span>
                        <form class="inline" method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="link-button" type="submit">ログアウト</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}">ログイン</a>
                        <a href="{{ route('register') }}">登録</a>
                    @endauth
                </div>
            </nav>

            <header class="topbar">
                <div>
                    <h1>{{ $heading ?? 'ToDo List' }}</h1>
                    <p>{{ $subheading ?? '今日やることをシンプルに管理します。' }}</p>
                </div>
                @isset($action)
                    {{ $action }}
                @endisset
            </header>

            @if (session('status'))
                <div class="notice">{{ session('status') }}</div>
            @endif

            {{ $slot }}
        </main>
    </body>
</html>
