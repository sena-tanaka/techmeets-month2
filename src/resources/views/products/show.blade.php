@extends('layouts.app')

@section('title', $product->name)

@section('content')
    <h2>{{ $product->name }}</h2>
    <p>価格: {{ $product->price }}円</p>
    <p>在庫数: {{ $product->stock }}</p>
    <p>カテゴリー: {{ $product->category }}</p>
    <p>{{ $product->description }}</p>

    <a href="{{ route('products.edit', $product) }}">編集</a>

    <form action="{{ route('products.destroy', $product) }}" method="POST" style="display:inline;">
        @csrf
        @method('DELETE')
        <button type="submit" onclick="return confirm('本当に削除しますか？')">削除</button>
    </form>

    <br><br>
    <a href="{{ route('products.index') }}">一覧に戻る</a>
@endsection
