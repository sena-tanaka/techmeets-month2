<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Reservation;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    // 予約を保存する(このイベントに対する予約)
    public function store(Request $request, Event $event)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email',
            'people' => 'required|integer|min:1',
        ]);

        // どのイベントへの予約かを追加してから保存する
        $validated['event_id'] = $event->id;

        Reservation::create($validated);

        return redirect()->route('events.show', $event)->with('success', '予約しました');
    }

    // 予約をキャンセル(削除)する
    public function destroy(Event $event, Reservation $reservation)
    {
        $reservation->delete();

        return redirect()->route('events.show', $event)->with('success', '予約をキャンセルしました');
    }
}
