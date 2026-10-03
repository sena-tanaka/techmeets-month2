<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'user_id',
        'product_name',
        'amount',
        'stripe_session_id',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
