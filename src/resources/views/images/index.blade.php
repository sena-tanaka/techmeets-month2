<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            画像アップロード（S3）
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- アップロードフォーム --}}
            <div class="p-6 bg-white shadow sm:rounded-lg">
                @if (session('status'))
                    <p class="mb-4 text-green-600">{{ session('status') }}</p>
                @endif

                <form method="POST" action="{{ route('images.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="image" accept="image/*" required>
                    @error('image')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <button type="submit" class="mt-4 px-4 py-2 bg-gray-800 text-white rounded">
                        アップロード
                    </button>
                </form>
            </div>

            {{-- 画像一覧 --}}
            <div class="p-6 bg-white shadow sm:rounded-lg">
                @if ($images->isEmpty())
                    <p class="text-gray-500">まだ画像がありません。</p>
                @else
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach ($images as $image)
                            <img src="{{ $image['url'] }}" alt="{{ $image['path'] }}"
                                 class="w-full h-40 object-cover rounded">
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
