<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// ログインが必要なページ
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 投稿の作成・編集・削除はログインユーザーのみ
    Route::resource('posts', PostController::class)->except(['index', 'show']);
});

// 投稿の一覧・詳細は誰でも見られる
Route::resource('posts', PostController::class)->only(['index', 'show']);

require __DIR__.'/auth.php';
