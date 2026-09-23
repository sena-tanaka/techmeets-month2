<div class="mb-4">
    <label class="block font-medium mb-1">タイトル</label>
    <input type="text" name="title" value="{{ old('title', $post->title ?? '') }}"
           class="w-full border-gray-300 rounded">
    @error('title') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
</div>

<div class="mb-4">
    <label class="block font-medium mb-1">カテゴリー</label>
    <input type="text" name="category" value="{{ old('category', $post->category ?? '') }}"
           class="w-full border-gray-300 rounded">
    @error('category') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
</div>

<div class="mb-4">
    <label class="block font-medium mb-1">本文</label>
    <textarea name="content" rows="10" class="w-full border-gray-300 rounded">{{ old('content', $post->content ?? '') }}</textarea>
    @error('content') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
</div>
