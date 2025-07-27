<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\PurchaseRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Helpers\MethodHelper;
use App\Models\Purchase;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class PurchaseController extends Controller
{
    public function show($id)
    {
        $item = Item::findOrFail($id);
        $user = Auth::user();
        $label = MethodHelper::getPaymentMethodLabel($item->payment_method);
        return view('purchase', compact('item', 'user', 'label'));
    }


    public function checkout(PurchaseRequest $request)
    {
        $request->validated();

        $item = Item::findOrFail($request->item_id);

        Stripe::setApiKey(config('stripe.stripe_secret_key'));

        $session = Session::create([
            'payment_method_types' => [$request->payment_method],
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
            'cancel_url' => route('purchase.cancel'),
            'metadata' => [
                'item_id'    => $item->id,
                'post_code'  => $request->post_code,
                'destination' => $request->destination,
            ],
        ]);

        return redirect($session->url);
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

            $item_id = $session->metadata->item_id ?? null;
            $user_id = auth()->id();

            if (!$item_id || !$user_id) {
                return redirect('/')->with('error', '購入情報が不足しています。');
            }

            $alreadyPurchased = Purchase::where('user_id', $user_id)
                ->where('item_id', $item_id)
                ->exists();

            if (!$alreadyPurchased) {
                Purchase::create([
                    'user_id'         => $user_id,
                    'item_id'         => $item_id,
                    'payment_method'  => $session->payment_method_types[0] ?? 'unknown',
                    'post_code'       => $session->metadata->post_code ?? '',
                    'destination'     => $session->metadata->destination ?? '',
                ]);
            }
            return redirect('/')->with('message', '購入しました。ありがとうございます。');
        } catch (\Exception $e) {
            return redirect('/')->with('error', '購入処理に失敗しました: ' . $e->getMessage());
        }
    }
    public function cancel($item_id)
    {
        return redirect()->route('purchase.show', ['id' => $item_id])
            ->with('error', '支払いがキャンセルされました。もう一度お試しください。');
    }
}
