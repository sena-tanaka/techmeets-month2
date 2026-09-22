<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = ['event_id', 'name', 'email', 'people'];

    // この予約がどのイベントのものか取得できるようにする
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
