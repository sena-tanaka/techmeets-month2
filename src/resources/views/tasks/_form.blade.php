@php
    $task = $task ?? null;
@endphp

<div class="mb-4">
    <label for="title" class="block font-medium mb-1">タイトル</label>
    <input type="text" id="title" name="title"
           value="{{ old('title', $task?->title) }}"
           class="w-full border-gray-300 rounded">
    @error('title')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

<div class="mb-4">
    <label for="description" class="block font-medium mb-1">説明</label>
    <textarea id="description" name="description" rows="4"
              class="w-full border-gray-300 rounded">{{ old('description', $task?->description) }}</textarea>
    @error('description')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

<div class="mb-4">
    <label for="due_date" class="block font-medium mb-1">期限</label>
    <input type="date" id="due_date" name="due_date"
           value="{{ old('due_date', $task?->due_date?->format('Y-m-d')) }}"
           class="border-gray-300 rounded">
    @error('due_date')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

@if ($task)
    <div class="mb-4">
        <input type="hidden" name="is_completed" value="0">
        <label class="inline-flex items-center">
            <input type="checkbox" name="is_completed" value="1"
                   @checked(old('is_completed', $task->is_completed))
                   class="rounded border-gray-300">
            <span class="ml-2">完了</span>
        </label>
    </div>
@endif
