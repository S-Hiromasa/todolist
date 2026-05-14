<x-layout title="チーム作成" heading="チーム作成" subheading="Todoを共有するワークスペースを作ります。">
    <x-slot:action>
        <a class="button secondary" href="{{ route('teams.index') }}">チーム一覧へ</a>
    </x-slot:action>

    <form class="form-panel" method="POST" action="{{ route('teams.store') }}">
        @csrf

        <div class="field">
            <label for="name">チーム名</label>
            <input id="name" name="name" value="{{ old('name') }}" maxlength="120" required autofocus>
            @error('name')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-actions">
            <a class="button secondary" href="{{ route('teams.index') }}">キャンセル</a>
            <button type="submit">作成</button>
        </div>
    </form>
</x-layout>
