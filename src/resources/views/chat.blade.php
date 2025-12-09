@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/chat.css')}}">
@endsection

@section('content')
<maiv class="main">
    <div class="sidebar">
        <h2 class="other-transactions">その他の取引</h2>
        @if(!$transactions->isEmpty())
        <div class="transactions__link">
            @foreach($transactions as $transaction)
            <a href="" class="transaction-items">
                $transaction->item->item_name
            </a>
            @endforeach
            @endif
        </div>
    </div>
    <div class="chat-content">
        <div class="user-info-block">
            <div class="user-images">
                <img
                    src="{{asset(optional($transaction->user->profile)->image ?? 'images/default.png')}}"
                    alt="{{$transaction->user->profile->user_name}}"
                    class="user-icon">
            </div> //取引相手のプロフィール画像
            <h2 class="chatpage-title">{{$transaction->user->profile->user_name}}さんとの取引画面</h2> //取引相手の名前
            @if(ログイン中の購入者なら)
            <button>取引を完了する</button>
            @endif
        </div>
        <div class="item-info-block">
            <div class="item_img">
                <img src="{{ asset($transaction->item->item_image ?: 'images/NoImage.png') }}" alt="{{ $transaction->item->item_name }}">
            </div>
            <div class="item_detail">
                <h1 class="item-of-name">商品名</h1>
                <p class="item-of-price">{{$transaction->item->item_price}}</p>
            </div>
        </div>
        <div class="chat-block" id="chatBlock">
            <div class="message">
            </div>
        </div>

    </div>
</maiv>

@endsection
