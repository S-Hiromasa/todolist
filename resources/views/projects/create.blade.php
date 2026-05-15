<x-layout title="プロジェクト作成" heading="プロジェクト作成" subheading="個人またはチームのプロジェクトを作成します。">
    <x-slot:action>
        <a class="button secondary" href="{{ route('projects.index') }}">一覧へ戻る</a>
    </x-slot:action>

    <form class="form-panel" method="POST" action="{{ route('projects.store') }}">
        @csrf

        <div class="field">
            <label for="name">プロジェクト名</label>
            <input id="name" name="name" value="{{ old('name') }}" maxlength="120" required autofocus>
            @error('name')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="field">
            <label for="team_id">所属</label>
            <select id="team_id" name="team_id">
                <option value="">個人プロジェクト</option>
                @foreach ($teams as $team)
                    <option value="{{ $team->id }}" @selected((string) old('team_id') === (string) $team->id)>
                        {{ $team->name }}
                    </option>
                @endforeach
            </select>
            @error('team_id')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="field">
            <label for="status">状態</label>
            <select id="status" name="status" required>
                <option value="active" @selected(old('status', 'active') === 'active')>進行中</option>
                <option value="completed" @selected(old('status') === 'completed')>完了</option>
                <option value="archived" @selected(old('status') === 'archived')>アーカイブ</option>
            </select>
            @error('status')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="field">
            <label for="deadline">プロジェクト期限</label>
            <input id="deadline" name="deadline" type="date" value="{{ old('deadline') }}">
            @error('deadline')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="field">
            <label for="description">説明</label>
            <textarea id="description" name="description">{{ old('description') }}</textarea>
            @error('description')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-actions">
            <a class="button secondary" href="{{ route('projects.index') }}">キャンセル</a>
            <button type="submit">作成</button>
        </div>
    </form>
</x-layout>
