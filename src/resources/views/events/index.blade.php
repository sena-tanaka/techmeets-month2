@extends('layouts.app')

@section('title', 'イベント一覧')

@section('content')
    <a href="{{ route('events.create') }}">新規イベント登録</a>

    @foreach ($events as $event)
        <div>
            <h2><a href="{{ route('events.show', $event) }}">{{ $event->title }}</a></h2>
            <p>開催日時: {{ $event->event_date }}</p>
        </div>
    @endforeach

    {{ $events->links() }}
@endsection
