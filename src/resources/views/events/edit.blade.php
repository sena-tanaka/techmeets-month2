@extends('layouts.app')

@section('title', 'イベント編集')

@section('content')
    <h2>イベント編集</h2>

    @if ($errors->any())
        <ul style="color: red;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('events.update', $event) }}" method="POST">
        @csrf
        @method('PUT')

        <label>イベント名</label><br>
        <input type="text" name="title" value="{{ old('title', $event->title) }}"><br>

        <label>開催日時</label><br>
        <input type="datetime-local" name="event_date" value="{{ old('event_date', $event->event_date) }}"><br>

        <label>説明</label><br>
        <textarea name="description" rows="6" cols="40">{{ old('description', $event->description) }}</textarea><br>

        <button type="submit">更新する</button>
    </form>
@endsection
