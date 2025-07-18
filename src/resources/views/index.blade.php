@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css')}}">
@endsection

@section('content')
<div class="market_content">
    <div class="market_content__inner">
        <div class="lists">
            <a href="{{ route('home', ['tab' => 'recommend','keyword' => request('keyword')]) }}" class="recommend {{ $tab === 'recommend' ? 'active' : '' }}">おすすめ</a>
            <a href="{{ route('home', ['tab' => 'mylist', 'keyword' => request('keyword')]) }}" class="my_list {{ $tab === 'mylist' ? 'active' : '' }}">マイリスト</a>
        </div>
        <div class="content__box">
            @if($items->isEmpty())
            <p>表示する商品がありません</p>
            @else
            @foreach($items as $item)
            <a href="{{ route('detail', $item) }}" class="content__id">
                <div class="content__box-item">
                    <div class="content__box__inner">
                        <div class="content__img">
                            <img src="{{asset($item->item_image ?: 'images/NoImage.png') }}" alt="{{$item->item_name}}">
                        </div>
                        <div class="content__name">
                            <p>{{$item->item_name}}</p>
                        </div>
                        @if($item->purchases)
                        <h2 class="sold">Sold</h2>
                        @endif
                    </div>
                </div>
            </a>
            @endforeach
            @endif
        </div>
    </div>
</div>
@if(method_exists($items, 'links'))
<div class="pagination">
    {{$items->appends(request()->except('page'))->links('vendor.pagination.default')}}
</div>
@endif
@endsection