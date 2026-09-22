@extends('layouts.app')

@section('title', 'イベント登録')

@section('content')
    <h2>イベント登録</h2>

    @if ($errors->any())
        <ul style="color: red;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('events.store') }}" method="POST">
        @csrf

        <label>イベント名</label><br>
        <input type="text" name="title" value="{{ old('title') }}"><br>

        <label>開催日時</label><br>
        <input type="datetime-local" name="event_date" value="{{ old('event_date') }}"><br>

        <label>説明</label><br>
        <textarea name="description" rows="6" cols="40">{{ old('description') }}</textarea><br>

        <button type="submit">登録する</button>
    </form>
@endsection
