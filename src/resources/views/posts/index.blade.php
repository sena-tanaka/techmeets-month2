@extends('layouts.app')

@section('title', '投稿一覧')

@section('content')
    <a href="{{ route('posts.create') }}">新規投稿</a>

    @foreach ($posts as $post)
        <div>
            <h2><a href="{{ route('posts.show', $post) }}">{{ $post->title }}</a></h2>
            <p>カテゴリー: {{ $post->category }}</p>
        </div>
    @endforeach

    {{ $posts->links() }}
@endsection
