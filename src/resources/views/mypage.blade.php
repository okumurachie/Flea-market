@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/mypage.css')}}">
@endsection

@section('content')

<div class="mypage_content">
    <div class="mypage_content__inner">
        @if($profile)
        <div class="profile__box">
            <div class="profile__content">
                <div class="profile_image">
                    @if(empty($profile->image))
                    <img src="{{ asset('images/default.png') }}" alt="デフォルト画像">
                    @else
                    <img id="preview" src="{{ asset($profile->image) }}" alt="プロフィール画像">
                    @endif
                </div>
                <div class="user_name">
                    <h2>{{$profile->user_name}}</h2>
                </div>
            </div>
            <div class="edit-profile">
                <a href="/mypage/profile" class="edit-profile__link">プロフィールを編集</a>
            </div>
        </div>
        @endif

        <div class="lists">
            <a href="{{ route('mypage', ['page' => 'sell']) }}" class="sell {{ $page === 'sell' ? 'active' : '' }}">出品した商品</a>
            <a href="{{ route('mypage', ['page' => 'buy']) }}" class="buy {{ $page === 'buy' ? 'active' : '' }}">購入した商品</a>
        </div>

        <div class="mypage-content__box">
            @if($page === 'sell')
            @if($items->isEmpty())
            <p>出品した商品はありません</p>
            @else
            @foreach($items as $item)
            <div class="mypage-content__box-item">
                <div class="content__img">
                    <img src="{{asset($item->item_image ?: 'images/NoImage.png') }}" alt="{{$item->item_name}}">
                </div>
                <div class="content__name">
                    <p>{{$item->item_name}}</p>
                </div>
            </div>
            @endforeach
            @endif
            @elseif($page === 'buy')
            @if($purchases->isEmpty())
            <p>購入履歴はありません</p>
            @else
            @foreach($purchases as $purchase)
            <div class="mypage-content__box-item">
                <div class="content__img">
                    <img src="{{ asset(optional($purchase->item)->item_image ?: 'images/NoImage.png') }}" alt="{{ optional($purchase->item)->item_name ?? '削除された商品です' }}">
                </div>
                <div class="content__name">
                    <p>{{ optional($purchase->item)->item_name ?? '削除された商品です' }}</p>
                </div>
            </div>
            @endforeach
            @endif
            @endif
        </div>
    </div>
</div>

<div class="pagination">
    @if($page === 'sell' && method_exists($items, 'links'))
    {{$items->appends(request()->except('page'))->links('vendor.pagination.default')}}
    @elseif($page === 'buy' && method_exists($purchases, 'links'))
    {{$purchases->appends(request()->except('page'))->links('vendor.pagination.default') }}
    @endif
</div>
@endsection