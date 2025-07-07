@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css')}}">
@endsection

@section('content')
<div class="market_content">
    <div class="market_content__inner">
        <div class="lists">
            <a href="" class="recommend">おすすめ</a>
            <a href="" class="my_list">マイリスト</a>
        </div>
        <div class="content__box">
            <h1 class="items">ここに商品が入ります。</h1>
        </div>
    </div>

</div>
@endsection