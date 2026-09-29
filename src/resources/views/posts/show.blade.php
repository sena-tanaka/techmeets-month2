@extends('layouts.app')

@section('title', $post->title)

@section('content')
    <h2>{{ $post->title }}</h2>
    <p>カテゴリー: {{ $post->category }}</p>
    <p>{{ $post->content }}</p>

    <a href="{{ route('posts.edit', $post) }}">編集</a>

    <form action="{{ route('posts.destroy', $post) }}" method="POST" style="display:inline;">
        @csrf
        @method('DELETE')
        <button type="submit" onclick="return confirm('本当に削除しますか？')">削除</button>
    </form>

    <br><br>
    <a href="{{ route('posts.index') }}">一覧に戻る</a>
@endsection
