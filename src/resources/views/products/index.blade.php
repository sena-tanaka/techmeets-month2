@extends('layouts.app')

@section('title', '商品一覧')

@section('content')
    <a href="{{ route('products.create') }}">新規登録</a>

    @foreach ($products as $product)
        <div>
            <h2><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></h2>
            <p>価格: {{ $product->price }}円 / 在庫: {{ $product->stock }} / カテゴリー: {{ $product->category }}</p>
        </div>
    @endforeach

    {{ $products->links() }}
@endsection
