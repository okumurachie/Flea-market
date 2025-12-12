@component('mail::message')
# 取引完了のお知らせ

{{ $purchase->item->user->name }} 様

購入者「**{{ $buyerName }}**」様が取引を完了しました。

**商品名:** {{ $itemName }}
**取引ID:** #{{ $purchase->id }}

あなたも評価を行うことで、取引が完全に完了します。

@component('mail::button', ['url' => url('/chat/' . $purchase->id)])
取引画面を開く
@endcomponent

このメールは自動送信されています。

{{ config('app.name') }}
@endcomponent
