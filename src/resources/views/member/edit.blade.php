<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">記事の編集</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 bg-white p-6 shadow-sm rounded">
            <form method="POST" action="{{ route('posts.update', $post) }}">
                @csrf
                @method('PUT')
                @include('posts._form')
                <button class="px-4 py-2 bg-blue-600 text-white rounded">更新する</button>
                <a href="{{ route('posts.show', $post) }}" class="ml-2 px-4 py-2 bg-gray-300 rounded">キャンセル</a>
            </form>
        </div>
    </div>
</x-app-layout>
