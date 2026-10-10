<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // 【追加】APIトークンを発行・確認する機能

class User extends Authenticatable
{
    use HasApiTokens; // 【追加】APIトークンを発行・確認する機能
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Post, $this> */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Task, $this> */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
