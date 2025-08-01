<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\PurchaseRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\Purchase;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;

use Illuminate\Support\Facades\Log;

class PurchaseController extends Controller
{
    public function show($id)
    {
        $item = Item::findOrFail($id);
        $user = Auth::user();
        return view('purchase', compact('item', 'user'));
    }


    public function checkout(PurchaseRequest $request)
    {
        $validated = $request->validated();
        $user = Auth::user();
        $user_id = $user->id;
        $item = Item::findOrFail($validated['item_id']);

        Stripe::setApiKey(config('stripe.stripe_secret_key'));

        $paymentMethod = $request->input('payment_method');

        if ($paymentMethod === 'card') {
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'jpy',
                        'product_data' => ['name' => $item->item_name],
                        'unit_amount' => $item->price,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('purchase.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('purchase.cancel', ['id' => $item->id]),
                'payment_intent_data' => [
                    'metadata' => [
                        'item_id' => $item->id,
                        'user_id' => $user_id,
                        'post_code'  => $validated['post_code'],
                        'destination' => $validated['destination'],
                    ],
                ],
            ]);
            return redirect($session->url);
        } elseif ($paymentMethod === 'konbini') {
            $paymentIntent = PaymentIntent::create([
                'payment_method_types' => ['konbini'],
                'currency' => 'jpy',
                'amount' => $item->price,
                'payment_method_data' => [
                    'type' => 'konbini',
                    'billing_details' => [
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                ],
                'confirm' => true,
                'metadata' => [
                    'item_id' => $item->id,
                    'user_id' => $user_id,
                    'item_name' => $item->item_name,
                    'post_code'  => $validated['post_code'],
                    'destination' => $validated['destination'],
                ],
                'payment_method_options' => [
                    'konbini' => [
                        'expires_after_days' => 3,
                    ],
                ],
            ]);
            Log::debug('PaymentIntent:', $paymentIntent->toArray());
            Log::debug('next_action:', (array) $paymentIntent->next_action);


            $voucherUrl = $paymentIntent->next_action->konbini_display_details->hosted_voucher_url ?? null;
            session([
                'voucher_url' => $voucherUrl,
                'item_id' => $item->id
            ]);
            return redirect()->route('konbini.confirm');
        }
        return redirect('/')->with('error', '支払い方法が無効です。');
    }
    public function success(Request $request)
    {
        $session_id = $request->get('session_id');

        if (!$session_id) {
            return redirect('/')->with('error', '決済情報が取得できませんでした。');
        }
        Stripe::setApiKey(config('stripe.stripe_secret_key'));

        try {
            $session = Session::retrieve($session_id);
            $paymentIntent = PaymentIntent::retrieve($session->payment_intent);

            $item_id = $paymentIntent->metadata->item_id ?? null;
            $user_id = $paymentIntent->metadata->user_id ?? null;
            $post_code = $paymentIntent->metadata->post_code ?? null;
            $destination = $paymentIntent->metadata->destination ?? null;

            if (!$item_id || !$user_id) {
                return redirect('/')->with('error', '購入情報が不足しています。');
            }

            $paymentMethod = $session->payment_method_types[0] ?? 'unknown';
            if ($paymentMethod === 'card') {
                $alreadyPurchased = Purchase::where('user_id', $user_id)
                    ->where('item_id', $item_id)
                    ->exists();

                if (!$alreadyPurchased) {
                    Purchase::create([
                        'user_id'         => $user_id,
                        'item_id'         => $item_id,
                        'payment_method'  => 'card',
                        'post_code'       => $post_code,
                        'destination'     => $destination,
                    ]);
                }
                return redirect('/')->with('message', '購入しました。ありがとうございます。');
            }
            return redirect('/')->with('error', '未対応の支払い方法です。');
        } catch (\Exception $e) {
            return redirect('/')->with('error', '購入処理に失敗しました: ' . $e->getMessage());
        }
    }

    public function showKonbiniConfirm()
    {
        logger('voucher_url: ' . session('voucher_url'));
        logger('item_id: ' . session('item_id'));

        $voucher_url = session('voucher_url');
        $itemId = session('item_id');

        if (!$voucher_url || !$itemId) {
            abort(404, '支払い情報が見つかりません。');
        }

        return view('konbini_confirm', compact('voucher_url', 'itemId'));
    }

    public function cancel($item_id)
    {
        return redirect()->route('purchase.show', ['id' => $item_id])
            ->with('error', '支払いがキャンセルされました。もう一度お試しください。');
    }
}
