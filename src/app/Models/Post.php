<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Post extends Model
{
    use HasFactory;

    // user_id は入れない（フォームから書き換えられないようにするため）
    protected $fillable = ['title', 'content', 'category'];

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // この投稿に「いいね」したユーザーたち（likesテーブル経由）
    public function likes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'likes')->withTimestamps();
    }
}
