<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $post->title }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 bg-white p-6 shadow-sm rounded">
            @if (session('success'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
            @endif

            <p class="text-sm text-gray-500 mb-4">
                {{ $post->user->name }}
                @if ($post->category) ・ {{ $post->category }} @endif
                ・ {{ $post->created_at->format('Y/m/d H:i') }}
            </p>

            {{-- e() でエスケープしてから改行を <br> に変換（XSS対策） --}}
            <div class="mb-6">{!! nl2br(e($post->content)) !!}</div>

            <div class="flex gap-2">
                @can('update', $post)
                    <a href="{{ route('posts.edit', $post) }}" class="px-4 py-2 bg-yellow-500 text-white rounded">編集</a>
                @endcan

                @can('delete', $post)
                    <form method="POST" action="{{ route('posts.destroy', $post) }}"
                          onsubmit="return confirm('本当に削除しますか？')">
                        @csrf
                        @method('DELETE')
                        <button class="px-4 py-2 bg-red-600 text-white rounded">削除</button>
                    </form>
                @endcan

                <a href="{{ route('posts.index') }}" class="px-4 py-2 bg-gray-300 rounded">一覧へ</a>
            </div>
        </div>
    </div>
</x-app-layout>
