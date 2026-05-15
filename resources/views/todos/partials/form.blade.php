@php
    $currentStatus = old('status', $todo?->status ?? 'todo');
    $currentAssignee = old('assignee_id', $todo?->assignee_id);
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

<div class="field">
    <label for="status">ステータス</label>
    <select id="status" name="status" required>
        <option value="todo" @selected($currentStatus === 'todo')>未着手</option>
        <option value="doing" @selected($currentStatus === 'doing')>進行中</option>
        <option value="done" @selected($currentStatus === 'done')>完了</option>
    </select>
    @error('status')
        <span class="error">{{ $message }}</span>
    @enderror
</div>

@isset($assignees)
    <div class="field">
        <label for="assignee_id">担当者</label>
        <select id="assignee_id" name="assignee_id">
            <option value="">未設定</option>
            @foreach ($assignees as $assignee)
                <option value="{{ $assignee->id }}" @selected((string) $currentAssignee === (string) $assignee->id)>
                    {{ $assignee->name }}
                </option>
            @endforeach
        </select>
        @error('assignee_id')
            <span class="error">{{ $message }}</span>
        @enderror
    </div>
@endisset

<div class="form-actions">
    <a class="button secondary" href="{{ $todo?->project_id ? route('projects.show', $todo->project_id) : ($todo?->team_id ? route('teams.show', $todo->team_id) : route('todos.index')) }}">キャンセル</a>
    <button type="submit">保存</button>
</div>
