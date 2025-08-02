<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use Stripe\Stripe;
use Illuminate\Support\Facades\Log;


class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();

        // Log::info('Webhook 受信', ['payload' => $payload]);

        $event = json_decode($payload, true);

        if (!isset($event['type'])) {
            return response()->json(['status' => 'invalid'], 400);
        }

        if ($event['type'] === 'payment_intent.succeeded') {
            $intent = $event['data']['object'];
            $metadata = $intent['metadata'];

            $user_id = $metadata['user_id'] ?? null;
            $item_id = $metadata['item_id'] ?? null;

            if (!$user_id || !$item_id) {
                // Log::warning('購入データのメタ情報が不足しています');
                return response()->json(['status' => 'missing metadata'], 400);
            }

            $alreadyPurchased = Purchase::where('user_id', $user_id)
                ->where('item_id', $item_id)
                ->exists();

            if (!$alreadyPurchased) {
                Purchase::create([
                    'user_id'        => $user_id,
                    'item_id'        => $item_id,
                    'payment_method' => $intent['payment_method_types'][0] ?? 'unknown',
                    'post_code'      => $metadata['post_code'] ?? null,
                    'destination'    => $metadata['destination'] ?? null,
                ]);

                // Log::info("コンビニ支払い完了により購入保存: user_id=$user_id, item_id=$item_id");
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
