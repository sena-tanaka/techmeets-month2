<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PostController;

// api.phpに書いたルートは、URLの先頭に自動で /api が付く
// → 実際のURLは GET /api/posts になる
Route::get('/posts', [PostController::class, 'index']);

// 【追加】記事の新規作成。auth:sanctum = 正しいトークンを持っている人だけ通す
Route::post('/posts', [PostController::class, 'store'])->middleware('auth:sanctum');
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
