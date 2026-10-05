<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\LikeController;

// Stripe からの Webhook 通知（Stripe が送ってくるので、ログイン不要）
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])->name('stripe.webhook');

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

    // 画像のアップロード・一覧（S3）はログインユーザーのみ
    Route::get('/images', [ImageController::class, 'index'])->name('images.index');
    Route::post('/images', [ImageController::class, 'store'])->name('images.store');

    // タスクはすべてログインユーザーのみ（自分のタスクだけ扱える）
    Route::resource('tasks', TaskController::class);
    Route::patch('/tasks/{task}/toggle', [TaskController::class, 'toggle'])->name('tasks.toggle');

    // いいね（押すたびに、いいね/取り消しが切り替わる）
    Route::post('/posts/{post}/like', [LikeController::class, 'toggle'])->name('posts.like');

    // Stripeテスト決済（商品ページ → 決済 → 完了ページ）
    Route::get('/shop', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'checkout'])->name('checkout');
    Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
});

// 投稿の一覧・詳細は誰でも見られる
Route::resource('posts', PostController::class)->only(['index', 'show']);

require __DIR__.'/auth.php';
