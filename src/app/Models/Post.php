<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    use HasFactory;

    // user_id は入れない（フォームから書き換えられないようにするため）
    protected $fillable = ['title', 'content', 'category'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
