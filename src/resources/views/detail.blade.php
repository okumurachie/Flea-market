@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css')}}">
@endsection

@section('content')
<div class="item_content">
    <div class="item_content_img">
        <img src="{{asset($item['item_image'])}}" alt="{{$item['item_name']}}">
    </div>
    <div class="item_information">
        <h2 class="item_name">{{$item['item_name']}}</h2>
        @if($item->brand)
        <p class="brand_name">{{$item['brand']}}</p>
        @endif
        <h3 class="item_price">
            <span class="yen-mark">¥</span>
            <span class="price-of-item">{{ number_format($item['price']) }}</span>
            <span class="tax-included">(税込)</span>
        </h3>
        <div class="favorite-and-comments">
            <div class="favorite-count" onclick="toggleFavorite({{ $item->id}})">
                @php
                $isFavorited = auth()->check() && $item->isFavoritedBy(auth()->user());
                @endphp

                <i id="favorite-icon"
                    class="{{$isFavorited ? 'fas' : 'far'}} fa-star"
                    style="color: #555; font-size: 24px;"></i>
                <span id="favorite-count">{{ $item->favorites()->count() }}</span>
            </div>
            <div class="comment-count">
                <i class="far fa-comment" style="color: #555; font-size: 24px;"></i>
                <span id="comment-count">{{ $item->comments()->count() }}</span>
            </div>
        </div>
        <a class="purchases_button" href="/purchases/{{$item['id']}}">
            購入手続きへ
        </a>
        <div class="item-description">
            <h3>商品説明</h3>
            <p class="description">{{$item['description']}}</p>
        </div>
        <div class="item-detail-information">
            <h3>商品の情報</h3>
            <div class="item-categories">
                <h4 class="categories">カテゴリー</h4>
                @foreach($item->categories as $category)
                <p class="category-tag">{{ $category->content}}</p>
                @endforeach
            </div>
            <div class="item-condition">
                <h4 class="condition">商品の状態</h4>
                <p class="condition-tag">{{$item->condition->condition}}</p>
            </div>
        </div>
        <div class="commemts-of-item">
            <h3>コメント({{$item->comments()->count()}})</h3>
            <div class="commets-by-users">
                @foreach($item->comments as $comment)
                <div class="comment-block">
                    <div class="user_images">
                        <img
                            src="{{asset(optional($comment->user->profile)->image ?? 'images/default.png')}}"
                            alt="{{$comment->user->profile->user_name}}"
                            class="user-icon">
                        <p class="user_name">{{$comment->user->profile->user_name}}</p>
                    </div>
                    <div class="comment-body">
                        <p class="comment-text">{{ $comment->comment }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        <div class="add-comment-form">
            <h4 class="add-comment">商品へのコメント</h4>
            <form action="{{route('comments.add')}}" class="add-comment-input-form" method="post">
                @csrf
                <input type="hidden" name="item_id" value="{{$item->id}}">
                <textarea name="comment" id="comment" class="input-comment-form"></textarea>
                <p class="input-comment__error-message">
                    @error('comment')
                    {{ $message }}
                    @enderror
                </p>
                <button class="add-comment__button" type="submit">コメントを送信する</button>
            </form>
        </div>
    </div>
</div>
<script>
    function toggleFavorite(itemId) {
        fetch('/item/favorite/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    item_id: itemId
                })
            })
            .then(response => response.json())
            .then(data => {
                const icon = document.getElementById('favorite-icon');
                const count = document.getElementById('favorite-count');

                if (data.status === 'added') {
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                    icon.style.color = 'gold';
                } else {
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                    icon.style.color = '#aaa';
                }

                count.textContent = data.count;
            })
            .catch(error => {
                alert('ログインが必要です。会員登録がまだの方は、登録お願いします');
                console.error(error);
            });
    }
</script>
@endsection