<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;
use Stripe\StripeClient;

class CheckoutController extends Controller
{
    // 練習用の商品リスト
    private array $products = [
        1 => ['name' => 'Tシャツ', 'price' => 2000],
        2 => ['name' => 'マグカップ', 'price' => 1200],
    ];

    // ① 商品ページ
    public function index()
    {
        return view('checkout.index', ['products' => $this->products]);
    }

    // ② Checkout Session を作って Stripe の決済ページへ移動
    public function checkout(Request $request)
    {
        $product = $this->products[$request->input('product_id')] ?? abort(404);

        $stripe = new StripeClient(config('services.stripe.secret'));

        $session = $stripe->checkout->sessions->create([
            'mode' => 'payment',
            'line_items' => [[
                'price_data' => [
                    'currency' => 'jpy',
                    'product_data' => ['name' => $product['name']],
                    'unit_amount' => $product['price'], // 円は小数なし → 2000 = ¥2,000
                ],
                'quantity' => 1,
            ]],
            'success_url' => route('checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.index'),
        ]);

        return redirect($session->url);
    }

    // ④ 完了ページ：Stripe に支払い済みか確認してから DB に保存
    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');
        if (!$sessionId) {
            abort(400, 'session_id がありません');
        }

        $stripe = new StripeClient(config('services.stripe.secret'));

        $session = $stripe->checkout->sessions->retrieve($sessionId, [
            'expand' => ['line_items'],
        ]);

        if ($session->payment_status !== 'paid') {
            abort(400, '支払いが完了していません');
        }

        // リロードしても二重登録されないように firstOrCreate を使う
        $purchase = Purchase::firstOrCreate(
            ['stripe_session_id' => $session->id],
            [
                'user_id' => auth()->id(),
                'product_name' => $session->line_items->data[0]->description,
                'amount' => $session->amount_total,
                'status' => $session->payment_status,
            ]
        );

        return view('checkout.success', compact('purchase'));
    }
}
