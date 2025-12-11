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
        <!-- @if(!$transactions->isEmpty()) ここにその他取引中の商品名があれば入ります。 並び順は新規メッセージが来た順に表示する -->
        <div class="transactions__links">
            @foreach($transactions as $transaction)
            <a href="{{route('chat.show', $transaction->id)}}" class="transaction-items">
                {{$transaction->item->item_name}}
            </a>
            @endforeach
            <!-- @endif -->
        </div>
    </div>

    <div class="chat-content">
        <div class="user-info-block">
            <div class="user-info--wrapper">
                @php
                $buyer = $purchase->user;
                $seller = $purchase->item->user;
                $partner = (Auth::id() === $buyer->id) ? $seller : $buyer;
                @endphp
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

            @if((string)Auth::id() === (string)$purchase->user_id)
            <form action="" method="POST" class="complete-form">
                @csrf
                <button class="complete-button">取引を完了する</button>
            </form>
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
            @forelse($messages as $message)
            @if($message->sender_id !== Auth::id())
            <div class="message">
                <div class="message-header">
                    <img
                        src="{{asset(optional($purchase->user->profile)->image ?? 'images/default.png')}}"
                        class="user-icon-image">
                    <p class="user-name">{{ optional($purchase->user->pofile)->user_name }}</p>
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
                <div class="button-group">
                    <form action="" method="POST">
                        @csrf
                        @method('DELETE')
                        <a href="" class="message-edit">編集</a>
                        <button class="chat-delete">削除</button>
                    </form>
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
    <script>
        const messageInput = document.getElementById('messageInput');
        const chatForm = document.getElementById('chatForm');
        document.getElementById('messageInput').addEventListener('input', function() {
            sessionStorage.setItem('chat_draft', this.value);
        });

        window.addEventListener('load', function() {
            const draft = sessionStorage.getItem('chat_draft');
            if (draft && !document.getElementById('messageInput').value) {
                document.getElementById('messageInput').value = draft;
            }
        });

        document.getElementById('chatForm').addEventListener('submit', function() {
            sessionStorage.removeItem('chat_draft');
        });
    </script>
</div>
@endsection
