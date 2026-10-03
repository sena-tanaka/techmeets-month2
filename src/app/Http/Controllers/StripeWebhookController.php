<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();                 // 届いた中身（そのままの文字列）
        $signature = $request->header('Stripe-Signature'); // Stripe が付けた署名
        $secret = config('services.stripe.webhook_secret');

        // ① 署名検証：本当に Stripe からの通知かを確認する
        try {
            $event = Webhook::constructEvent($payload, $signature, $secret);
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe Webhook: 中身の形式が不正です');
            return response('Invalid payload', 400);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe Webhook: 署名の検証に失敗しました');
            return response('Invalid signature', 400);
        }

        // ② 支払い成功の通知なら、決済IDをログに書く
        if ($event->type === 'payment_intent.succeeded') {
            $paymentIntent = $event->data->object;

            Log::info('決済完了（payment_intent.succeeded）', [
                'payment_intent_id' => $paymentIntent->id,
                'amount' => $paymentIntent->amount,
                'currency' => $paymentIntent->currency,
            ]);
        }

        // Stripe には「受け取りました」と 200 を返す
        return response('OK', 200);
    }
}
