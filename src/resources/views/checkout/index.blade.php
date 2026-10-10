<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            ショップ（Stripeテスト決済）
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @foreach ($products as $id => $product)
                <div class="bg-white shadow-sm sm:rounded-lg p-6 flex items-center justify-between">
                    <div>
                        <p class="text-lg font-semibold">{{ $product['name'] }}</p>
                        <p class="text-gray-600">¥{{ number_format($product['price']) }}</p>
                    </div>

                    <form method="POST" action="{{ route('checkout') }}">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $id }}">
                        <x-primary-button>
                            購入する
                        </x-primary-button>
                    </form>
                </div>
            @endforeach

            <p class="text-sm text-gray-500">
                ※テストモードです。カード番号 4242 4242 4242 4242 で決済できます（実際の請求は発生しません）。
            </p>
        </div>
    </div>
</x-app-layout>
