<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    // イベント一覧を表示
    public function index()
    {
        $events = Event::latest()->paginate(5);
        return view('events.index', compact('events'));
    }

    // イベント登録フォームを表示
    public function create()
    {
        return view('events.create');
    }

    // イベントを保存する
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'description' => 'required',
            'event_date' => 'required|date',
        ]);

        Event::create($validated);

        return redirect()->route('events.index')->with('success', 'イベントを登録しました');
    }

    // イベント詳細を表示(予約フォーム&予約一覧もここに含める)
    public function show(Event $event)
    {
        // このイベントに紐づく予約も一緒に取得する
        $reservations = $event->reservations;
        return view('events.show', compact('event', 'reservations'));
    }

    // 編集フォームを表示
    public function edit(Event $event)
    {
        return view('events.edit', compact('event'));
    }

    // イベントの更新を保存する
    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'description' => 'required',
            'event_date' => 'required|date',
        ]);

        $event->update($validated);

        return redirect()->route('events.index')->with('success', 'イベントを更新しました');
    }

    // イベントを削除する
    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('events.index')->with('success', 'イベントを削除しました');
    }
}
