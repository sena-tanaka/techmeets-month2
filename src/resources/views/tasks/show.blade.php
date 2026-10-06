<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">タスクの詳細</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 bg-white p-6 shadow-sm rounded">
            @if (session('success'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
            @endif

            <h3 class="text-2xl font-bold mb-2">{{ $task->title }}</h3>
            <p class="text-sm text-gray-500 mb-4">
                状態：{{ $task->is_completed ? '完了' : '未完了' }}
                ・ 期限：{{ $task->due_date?->format('Y/m/d') ?? 'なし' }}
            </p>
            <p class="whitespace-pre-wrap mb-6">{{ $task->description }}</p>

            <div class="flex gap-2">
                <a href="{{ route('tasks.edit', $task) }}" class="px-4 py-2 bg-blue-600 text-white rounded">編集</a>

                <form method="POST" action="{{ route('tasks.toggle', $task) }}">
                    @csrf
                    @method('PATCH')
                    <button class="px-4 py-2 bg-green-600 text-white rounded">
                        {{ $task->is_completed ? '未完了に戻す' : '完了にする' }}
                    </button>
                </form>

                <form method="POST" action="{{ route('tasks.destroy', $task) }}"
                      onsubmit="return confirm('本当に削除しますか？');">
                    @csrf
                    @method('DELETE')
                    <button class="px-4 py-2 bg-red-600 text-white rounded">削除</button>
                </form>

                <a href="{{ route('tasks.index') }}" class="px-4 py-2 bg-gray-300 rounded">一覧に戻る</a>
            </div>
        </div>
    </div>
</x-app-layout>
