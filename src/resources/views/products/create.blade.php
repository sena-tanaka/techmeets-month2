@extends('layouts.app')

@section('title', '商品登録')

@section('content')
    <h2>商品登録</h2>

    @if ($errors->any())
        <ul style="color: red;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('products.store') }}" method="POST">
        @csrf

        <label>商品名</label><br>
        <input type="text" name="name" value="{{ old('name') }}"><br>

        <label>価格</label><br>
        <input type="number" name="price" step="0.01" value="{{ old('price') }}"><br>

        <label>在庫数</label><br>
        <input type="number" name="stock" value="{{ old('stock') }}"><br>

        <label>カテゴリー</label><br>
        <input type="text" name="category" value="{{ old('category') }}"><br>

        <label>説明</label><br>
        <textarea name="description" rows="6" cols="40">{{ old('description') }}</textarea><br>

        <button type="submit">登録する</button>
    </form>
@endsection
