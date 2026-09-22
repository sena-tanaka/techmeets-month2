<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = ['title', 'description', 'event_date'];

    // このイベントに紐づく予約を全部取得できるようにする
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
