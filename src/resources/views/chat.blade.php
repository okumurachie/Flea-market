@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/chat.css')}}">
@endsection

@section('content')
<div class="main">
    <div class="sidebar">
        <div class="sidebar-header">
            <h2 class="other-transactions">その他の取引</h2>
        </div>
        <div class="transactions__links">
            @foreach($transactions as $transaction)
            <a href="{{route('chat.show', $transaction->id)}}" class="transaction-items">
                {{$transaction->item->item_name}}
            </a>
            @endforeach
        </div>
    </div>

    <div class="chat-content">
        @php
        $buyer = $purchase->user;
        $seller = $purchase->item->user;
        $partner = (Auth::id() === $buyer->id) ? $seller : $buyer;
        $isSeller = Auth::id() === $seller->id;
        @endphp
        <div class="user-info-block">
            <div class="user-info--wrapper">
                <div class="user-images">
                    <img
                        src="{{ asset(optional($partner->profile)->image ?? 'images/default.png') }}"
                        alt="{{ optional($purchase->user->profile)->user_name ?? 'ユーザー' }}"
                        class="user-icon">
                </div>
                <h2 class="chatpage-title">
                    {{ optional($partner->profile)->user_name }}さんとの取引画面
                </h2>
            </div>

            @if(!$isSeller && $purchase->transaction_status === 'in_progress')
            <button class="complete-button" id="completeTransactionBtn" type="button">取引を完了する</button>
            @endif
        </div>
        <div class="item-info-block">
            <div class="item_img">
                <img src="{{ asset($purchase->item->item_image ?: 'images/NoImage.png') }}" alt="{{ $purchase->item->item_name }}">
            </div>
            <div class="item_detail">
                <h1 class="item-of-name">{{ $purchase->item->item_name }}</h1>
                <p class="item-of-price">¥{{ number_format($purchase->item->price) }}</p>
            </div>
        </div>

        <div class="chat-block" id="chatBlock">
            <div class="message-display">
                @forelse($messages as $message)
                @if($message->sender_id !== Auth::id())
                <div class="message">
                    <div class="message-header">
                        <img
                            src="{{asset(optional($partner->profile)->image ?? 'images/default.png')}}"
                            class="user-icon-image">
                        <p class="user-name">{{ optional($partner->profile)->user_name }}</p>
                    </div>
                    <div class="message-space">
                        {!! nl2br(e($message->message)) !!}
                    </div>
                    @if($message->chat_image)
                    <div class="image-space">
                        <img src="{{ asset($message->chat_image) }}" class="message-image">
                    </div>
                    @endif
                </div>
                @else
                <div class="own-message">
                    <div class="message-header">
                        <p class="user-name">{{ Auth::user()->profile->user_name}}</p>
                        <img id="preview" src="{{ asset(Auth::user()->profile->image ?? 'images/default.png') }}" class="user-icon-image">
                    </div>
                    <div class="message-space own">
                        {!! nl2br(e($message->message)) !!}
                    </div>
                    @if($message->chat_image)
                    <div class="image-space own">
                        <img src="{{ asset($message->chat_image) }}" class="message-image">
                    </div>
                    @endif
                    <div class="message-actions" data-id="{{ $message->id }}">
                        <div class="edit-message">
                            <div class="form-button-group">
                                <a href="javascript:void(0)" class="message-edit" type="button">編集</a>
                                <button class="message-delete">削除</button>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @empty
                <p class="no-message">メッセージはありません</p>
                @endforelse
            </div>
            <form id="chatForm" action="{{route('chat.store', $purchase->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="chat-input-block">
                    @if ($errors->any())
                    <div class="chat-form__error-wrap">
                        @foreach ($errors->all() as $error)
                        <p class="chat-form__error-message">{{ $error }}</p>
                        @endforeach
                    </div>
                    @endif
                    <div class="input-wrapper">
                        <textarea class="chat-textarea" name="text" id="messageInput" placeholder="取引メッセージを記入してください">{{ old('text')}}</textarea>
                        <div class="image-inpput-wrapper">
                            <label for="imageInput" class="chat-file-label">画像を追加</label>
                            <input type="file" id="imageInput" accept="image/jpeg, image/png" name="image" class="visually-hidden">
                        </div>
                        <div class="send-button">
                            <button>
                                <img src="{{asset('images/inputbutton.png')}}" alt="送信" class="inputbutton-image">
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal-overlay" id="ratingModal" style="display: none;">
    <div class="modal-content">
        <h2 class="modal-title">取引が完了しました。</h2>
        <div class="rating-block">
            <p class="modal-text">今回の取引相手はどうでしたか？</p>
            <div class="star-rating" id="starRating">
                <span class="star" data-rating="1">★</span>
                <span class="star" data-rating="2">★</span>
                <span class="star" data-rating="3">★</span>
                <span class="star" data-rating="4">★</span>
                <span class="star" data-rating="5">★</span>
            </div>
        </div>
        <div class="rating-button">
            <button class="submit-rating-btn" id="submitRatingBtn">送信する</button>
        </div>
    </div>
</div>
<script src="{{ asset('js/chat.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        initRatingSystem({
            isSeller: {{ $isSeller ? 'true' : 'false' }},
            purchaseId: {{ $purchase->id }},
            showModalOnLoad: {{ ($isSeller && $purchase->transaction_status === 'buyer_completed') ? 'true' : 'false' }}
        });
    });
</script>
@endsection
