@extends('layouts.app')

@section('title', '投稿編集')

@section('content')
    <h2>投稿編集</h2>

    @if ($errors->any())
        <ul style="color: red;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('posts.update', $post) }}" method="POST">
        @csrf
        @method('PUT')

        <label>タイトル</label><br>
        <input type="text" name="title" value="{{ old('title', $post->title) }}"><br>

        <label>カテゴリー</label><br>
        <input type="text" name="category" value="{{ old('category', $post->category) }}"><br>

        <label>本文</label><br>
        <textarea name="content" rows="6" cols="40">{{ old('content', $post->content) }}</textarea><br>

        <button type="submit">更新する</button>
    </form>
@endsection
