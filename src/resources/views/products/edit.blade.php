@extends('layouts.app')

@section('title', '商品編集')

@section('content')
    <h2>商品編集</h2>

    @if ($errors->any())
        <ul style="color: red;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('products.update', $product) }}" method="POST">
        @csrf
        @method('PUT')

        <label>商品名</label><br>
        <input type="text" name="name" value="{{ old('name', $product->name) }}"><br>

        <label>価格</label><br>
        <input type="number" name="price" step="0.01" value="{{ old('price', $product->price) }}"><br>

        <label>在庫数</label><br>
        <input type="number" name="stock" value="{{ old('stock', $product->stock) }}"><br>

        <label>カテゴリー</label><br>
        <input type="text" name="category" value="{{ old('category', $product->category) }}"><br>

        <label>説明</label><br>
        <textarea name="description" rows="6" cols="40">{{ old('description', $product->description) }}</textarea><br>

        <button type="submit">更新する</button>
    </form>
@endsection
