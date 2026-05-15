@php
    $isDone = old('is_done', $todo?->is_done ?? false);
@endphp

<div class="field">
    <label for="title">タイトル</label>
    <input id="title" name="title" value="{{ old('title', $todo?->title) }}" maxlength="120" required>
    @error('title')
        <span class="error">{{ $message }}</span>
    @enderror
</div>

<div class="field">
    <label for="due_date">期限</label>
    <input id="due_date" name="due_date" type="date" value="{{ old('due_date', optional($todo?->due_date)->format('Y-m-d')) }}">
    @error('due_date')
        <span class="error">{{ $message }}</span>
    @enderror
</div>

<div class="field">
    <label for="description">メモ</label>
    <textarea id="description" name="description">{{ old('description', $todo?->description) }}</textarea>
    @error('description')
        <span class="error">{{ $message }}</span>
    @enderror
</div>

<label class="checkbox">
    <input name="is_done" type="checkbox" value="1" @checked($isDone)>
    完了済みにする
</label>

<div class="form-actions">
    <a class="button secondary" href="{{ $todo?->team_id ? route('teams.show', $todo->team_id) : route('todos.index') }}">キャンセル</a>
    <button type="submit">保存</button>
</div>
