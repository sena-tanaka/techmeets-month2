<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">ブログ記事一覧</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
            @endif

            @auth
                <a href="{{ route('posts.create') }}" class="inline-block mb-4 px-4 py-2 bg-blue-600 text-white rounded">新規投稿</a>
            @endauth

            @forelse ($posts as $post)
                <div class="bg-white p-6 mb-4 shadow-sm rounded">
                    <h3 class="text-lg font-bold">
                        <a href="{{ route('posts.show', $post) }}" class="hover:underline">{{ $post->title }}</a>
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $post->user->name }}
                        @if ($post->category) ・ {{ $post->category }} @endif
                        ・ {{ $post->created_at->format('Y/m/d H:i') }}
                    </p>
                </div>
            @empty
                <p>まだ記事がありません。</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
