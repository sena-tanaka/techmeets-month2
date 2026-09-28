<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">タスクの作成</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 bg-white p-6 shadow-sm rounded">
            <form method="POST" action="{{ route('tasks.store') }}">
                @csrf
                @include('tasks._form')
                <button class="px-4 py-2 bg-blue-600 text-white rounded">作成する</button>
                <a href="{{ route('tasks.index') }}" class="ml-2 px-4 py-2 bg-gray-300 rounded">キャンセル</a>
            </form>
        </div>
    </div>
</x-app-layout>
