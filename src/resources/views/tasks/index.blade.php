<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">タスク一覧</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
            @endif

            <a href="{{ route('tasks.create') }}" class="inline-block mb-4 px-4 py-2 bg-blue-600 text-white rounded">新規タスク</a>

            @forelse ($tasks as $task)
                <div class="bg-white p-4 mb-3 shadow-sm rounded flex items-center justify-between">
                    <div>
                        <a href="{{ route('tasks.show', $task) }}"
                           class="font-bold hover:underline {{ $task->is_completed ? 'line-through text-gray-400' : '' }}">
                            {{ $task->title }}
                        </a>
                        <p class="text-sm text-gray-500 mt-1">
                            期限：{{ $task->due_date?->format('Y/m/d') ?? 'なし' }}
                        </p>
                    </div>

                    <form method="POST" action="{{ route('tasks.toggle', $task) }}">
                        @csrf
                        @method('PATCH')
                        <button class="px-3 py-1 rounded text-sm {{ $task->is_completed ? 'bg-gray-300' : 'bg-green-600 text-white' }}">
                            {{ $task->is_completed ? '未完了に戻す' : '完了にする' }}
                        </button>
                    </form>
                </div>
            @empty
                <p>まだタスクがありません。</p>
            @endforelse

            <div class="mt-6">
                {{ $tasks->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
