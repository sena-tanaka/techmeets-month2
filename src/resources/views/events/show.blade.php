@extends('layouts.app')

@section('title', $event->title)

@section('content')
    {{-- イベント情報 --}}
    <h2>{{ $event->title }}</h2>
    <p>開催日時: {{ $event->event_date }}</p>
    <p>{{ $event->description }}</p>

    <a href="{{ route('events.edit', $event) }}">編集</a>

    <form action="{{ route('events.destroy', $event) }}" method="POST" style="display:inline;">
        @csrf
        @method('DELETE')
        <button type="submit" onclick="return confirm('本当に削除しますか？')">イベントを削除</button>
    </form>

    <hr>

    {{-- 予約フォーム --}}
    <h3>予約する</h3>

    @if ($errors->any())
        <ul style="color: red;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('events.reservations.store', $event) }}" method="POST">
        @csrf

        <label>お名前</label><br>
        <input type="text" name="name" value="{{ old('name') }}"><br>

        <label>メールアドレス</label><br>
        <input type="email" name="email" value="{{ old('email') }}"><br>

        <label>人数</label><br>
        <input type="number" name="people" value="{{ old('people') }}"><br>

        <button type="submit">予約する</button>
    </form>

    <hr>

    {{-- 予約一覧 --}}
    <h3>予約一覧</h3>

    @forelse ($reservations as $reservation)
        <div>
            <p>{{ $reservation->name }} 様 ({{ $reservation->people }}名) - {{ $reservation->email }}</p>
            <form action="{{ route('events.reservations.destroy', [$event, $reservation]) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" onclick="return confirm('この予約をキャンセルしますか？')">キャンセル</button>
            </form>
        </div>
    @empty
        <p>まだ予約はありません。</p>
    @endforelse

    <br>
    <a href="{{ route('events.index') }}">イベント一覧に戻る</a>
@endsection
