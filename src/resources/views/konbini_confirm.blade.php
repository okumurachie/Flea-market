@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/konbini_confirm.css') }}">
@endsection

@section('content')
<div class="confirm-content">
    <h2>注文を受け付けました</h2>
    <p class="print_link-message">以下のリンクから支払い情報を表示・印刷してください。</p>
    <a href="{{$voucher_url}}" target="_blank" rel="noopener noreferrer" class="print_link">支払い情報を表示する</a>
    <p class="precautions">お支払い完了後、商品を発送いたします。</p>
    <a href="{{route('home')}}" class="back-home">ホームに戻る</a>
</div>
@endsection