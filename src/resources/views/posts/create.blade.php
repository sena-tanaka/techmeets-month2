@extends('layouts.app')

@section('title', '新規投稿')

@section('content')
    <h2>新規投稿</h2>

    @if ($errors->any())
        <ul style="color: red;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('posts.store') }}" method="POST">
        @csrf

        <label>タイトル</label><br>
        <input type="text" name="title" value="{{ old('title') }}"><br>

        <label>カテゴリー</label><br>
        <input type="text" name="category" value="{{ old('category') }}"><br>

        <label>本文</label><br>
        <textarea name="content" rows="6" cols="40">{{ old('content') }}</textarea><br>

        <button type="submit">投稿する</button>
    </form>
@endsection
