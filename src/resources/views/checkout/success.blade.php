<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            購入完了
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-2">
                <p class="text-lg font-semibold">購入ありがとうございました！</p>
                <p>商品：{{ $purchase->product_name }}</p>
                <p>金額：¥{{ number_format($purchase->amount) }}</p>
                <p>購入日時：{{ $purchase->created_at->format('Y/m/d H:i') }}</p>

                <a href="{{ route('checkout.index') }}"
                   class="inline-block mt-4 text-indigo-600 hover:underline">
                    ← 商品一覧へ戻る
                </a>
            </div>
        </div>
    </div>
</x-app-layout>