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
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

        if (e.target.classList.contains('message-delete')) {
            const ownMessageDiv = e.target.closest('.own-message');
            const messageDiv = ownMessageDiv.querySelector('.message-actions');

            console.log('ownMessageDiv:', ownMessageDiv);
            console.log('messageDiv:', messageDiv);
            console.log('dataset.id:', messageDiv?.dataset.id);

            if (!messageDiv?.dataset.id) {
                console.error('IDが取得できません');
                return;
            }


            // if (!messageDiv?.dataset.id) return;

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
                    alert('削除に失敗しました');
                }
            } catch (error) {
                alert('エラーが発生しました');
            }
        }

        if (e.target.classList.contains('message-edit')) {
            const ownMessageDiv = e.target.closest('.own-message');
            const messageSpace = ownMessageDiv?.querySelector('.message-space.own');

            if (!messageSpace || ownMessageDiv.querySelector('.edit-input')) return;

            const originalHTML = messageSpace.innerHTML;
            const original = messageSpace.textContent.trim();

            messageSpace.innerHTML = `
            <textarea class="edit-input" rows="3">${original}</textarea>
            <div class="edit-buttons">
                <button class="edit-save">保存</button>
                <button class="edit-cancel">キャンセル</button>
            </div>
        `;
            messageSpace.dataset.originalHtml = originalHTML;
        }

        if (e.target.classList.contains('edit-cancel')) {
            const ownMessageDiv = e.target.closest('.own-message');
            const messageSpace = ownMessageDiv.querySelector('.message-space.own');
            const originalHtml = messageSpace.dataset.originalHtml;

            messageSpace.innerHTML = originalHtml;
            delete messageSpace.dataset.originalHtml;
        }

        if (e.target.classList.contains('edit-save')) {
            const ownMessageDiv = e.target.closest('.own-message');
            const messageDiv = ownMessageDiv.querySelector('.message-actions');
            const input = ownMessageDiv.querySelector('.edit-input');
            const messageSpace = ownMessageDiv.querySelector('.message-space.own');

            console.log('edit-save - messageDiv:', messageDiv);
            console.log('edit-save - dataset.id:', messageDiv?.dataset.id);

            if (!messageDiv?.dataset.id || !input || !messageSpace) return;

            const id = messageDiv.dataset.id;
            const newText = input.value.trim();

            if (!newText) {
                alert('メッセージを入力してください');
                return;
            }

            try {
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

                if (res.ok) {
                    messageSpace.innerHTML = newText.replace(/\n/g, '<br>');
                    delete messageSpace.dataset.originalHtml;
                } else {
                    alert('保存に失敗しました');
                }
            } catch (error) {
                alert('エラーが発生しました');
            }
        }
    });

    const ratingModal = document.getElementById('ratingModal');
    const starRating = document.getElementById('starRating');
    const submitRatingBtn = document.getElementById('submitRatingBtn');
    const completeTransactionBtn = document.getElementById('completeTransactionBtn');
    let selectedRating = 0;

    console.log('評価スクリプト読み込み完了');

    // ページ読み込み時、出品者で購入者が評価済みの場合はモーダルを表示
    @if($isSeller && $purchase -> transaction_status === 'buyer_completed')
    ratingModal.style.display = 'flex';
    @endif


    // 取引完了ボタン（購入者用）
    if (completeTransactionBtn) {
        completeTransactionBtn.addEventListener('click', function(e) {
            e.preventDefault();
            ratingModal.style.display = 'flex';
        });
    }


    // 星評価のホバーとクリック処理
    const stars = starRating.querySelectorAll('.star');
    stars.forEach(star => {
        star.addEventListener('mouseenter', function() {
            const rating = parseInt(this.dataset.rating);
            highlightStars(rating);
        });

        star.addEventListener('click', function() {
            selectedRating = parseInt(this.dataset.rating);
            console.log('クリック - 選択された評価:', selectedRating);
            highlightStars(selectedRating);
        });
    });

    starRating.addEventListener('mouseleave', function() {
        highlightStars(selectedRating);
    });

    function highlightStars(rating) {
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.add('active');
            } else {
                star.classList.remove('active');
            }
        });
    }

    // 評価送信
    submitRatingBtn.addEventListener('click', async function() {
        if (selectedRating === 0) {
            alert('評価を選択してください');
            return;
        }

        const isSeller = {{ $isSeller ? 'true' : 'false' }};
        const purchaseId = {{ $purchase->id }};


        try {
            const response = await fetch(`/chat/${purchaseId}/complete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    rating: selectedRating,
                    is_seller: isSeller
                })
            });

            if (response.ok) {
                // ホーム画面に遷移
                window.location.href = '/';
            } else {
                alert('評価の送信に失敗しました');
            }
        } catch (error) {
            console.error('評価送信エラー:', error);
            alert('評価の送信に失敗しました');
        }
    });
</script>
@endsection
