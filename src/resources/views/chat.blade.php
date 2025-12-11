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
        @php
        $buyer = $purchase->user;
        $seller = $purchase->item->user;
        $partner = (Auth::id() === $buyer->id) ? $seller : $buyer;
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
                <div class="message" data-id="{{ $message->id }}">
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

            document.addEventListener('click', async function(e) {
                // 1. メッセージ削除
                if (e.target.classList.contains('message-delete')) {
                    const ownMessageDiv = e.target.closest('.own-message');
                    const messageDiv = ownMessageDiv.querySelector('.message');

                    if (!messageDiv || !messageDiv.dataset.id) {
                        console.error('メッセージIDが見つかりません');
                        return;
                    }

                    const id = messageDiv.dataset.id;

                    if (!confirm('このメッセージを削除しますか?')) return;

                    try {
                        const res = await fetch(`/messages/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });

                        if (res.ok) {
                            ownMessageDiv.remove();
                        } else {
                            const errorData = await res.json();
                            console.error('削除エラー:', errorData);
                            alert('削除に失敗しました: ' + (errorData.message || '不明なエラー'));
                        }
                    } catch (error) {
                        alert('エラーが発生しました');
                    }
                }

                // 2. メッセージ編集開始
                if (e.target.classList.contains('message-edit')) {
                    const ownMessageDiv = e.target.closest('.own-message');

                    if (!ownMessageDiv) {
                        console.error('own-message要素が見つかりません');
                        return;
                    }

                    const messageSpace = ownMessageDiv.querySelector('.message-space.own');
                    const originalHTML = messageSpace.innerHTML;
                    const original = messageSpace.textContent.trim();

                    // すでに入力中なら何もしない
                    if (ownMessageDiv.querySelector('.edit-input')) return;

                    // inputに置き換える
                    messageSpace.innerHTML = `
            <textarea class="edit-input" rows="3">${original}</textarea>
            <div class="edit-buttons">
                <button class="edit-save">保存</button>
                <button class="edit-cancel">キャンセル</button>
            </div>
        `;

                    // 元のHTMLを保存しておく
                    messageSpace.dataset.originalHtml = originalHTML;
                }

                // 3. 編集キャンセル
                if (e.target.classList.contains('edit-cancel')) {
                    const ownMessageDiv = e.target.closest('.own-message');
                    const messageSpace = ownMessageDiv.querySelector('.message-space.own');
                    const originalHtml = messageSpace.dataset.originalHtml;

                    // 元のHTMLに戻す
                    messageSpace.innerHTML = originalHtml;
                    delete messageSpace.dataset.originalHtml;
                }

                // 4. 編集保存
                if (e.target.classList.contains('edit-save')) {
                    console.log('保存ボタンがクリックされました');

                    const ownMessageDiv = e.target.closest('.own-message');
                    console.log('ownMessageDiv:', ownMessageDiv);

                    if (!ownMessageDiv) {
                        console.error('own-message要素が見つかりません');
                        alert('エラー: メッセージ要素が見つかりません');
                        return;
                    }

                    const messageDiv = ownMessageDiv.querySelector('.message');
                    console.log('messageDiv:', messageDiv);

                    if (!messageDiv || !messageDiv.dataset.id) {
                        console.error('メッセージIDが見つかりません');
                        alert('エラー: メッセージIDが見つかりません');
                        return;
                    }

                    const id = messageDiv.dataset.id;
                    console.log('メッセージID:', id);

                    const input = ownMessageDiv.querySelector('.edit-input');
                    console.log('input:', input);

                    const messageSpace = ownMessageDiv.querySelector('.message-space.own');
                    console.log('messageSpace:', messageSpace);

                    if (!input) {
                        console.error('入力欄が見つかりません');
                        alert('エラー: 入力欄が見つかりません');
                        return;
                    }

                    const newText = input.value.trim();
                    console.log('新しいテキスト:', newText);

                    if (!newText) {
                        alert('メッセージを入力してください');
                        return;
                    }

                    try {
                        console.log('リクエスト送信中...');
                        const res = await fetch(`/messages/${id}`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                body: newText
                            })
                        });

                        console.log('レスポンスステータス:', res.status);

                        if (res.ok) {
                            console.log('保存成功');
                            // 改行を<br>に変換して表示
                            messageSpace.innerHTML = newText.replace(/\n/g, '<br>');
                            delete messageSpace.dataset.originalHtml;
                        } else {
                            const errorData = await res.json();
                            console.error('保存エラー:', errorData);
                            alert('保存に失敗しました: ' + (errorData.message || '不明なエラー'));
                        }
                    } catch (error) {
                        console.error('例外エラー:', error);
                        alert('エラーが発生しました: ' + error.message);
                    }
                }
            });
        </script>
    </div>
    @endsection
