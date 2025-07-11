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
        <p class="brad_name">{{$item['brand']}}</p>
        @endif
        <h3 class="item_price">
            <span class="yen-mark">¥</span>
            <span class="price-of-item">{{ number_format($item['price']) }}</span>
            <span class="tax-included">(税込)</span>
        </h3>
        <div class="favorite-and-comments">
            <div class="favorite-count">
                <i class="far fa-star" style="color: gold; font-size: 24px;"></i>
                <p>{{ $item->favorites_count }}</p>
            </div>
            <div class="comment-count">
                <i class="far fa-comment" style="color: #555; font-size: 24px;"></i>
                <p>{{ $item->favorites_count }}</p>
            </div>
        </div>
    </div>
</div>
@endsection