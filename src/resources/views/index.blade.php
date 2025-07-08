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
            @foreach($items as $item)
            <a href="" class="content__id">
                <div class="content__box-item">
                    <div class="content__box__inner">
                        <div class="content__img">
                            <img src="{{asset($item->item_image)}}" alt="{{$item->item_image}}">
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
        </div>
    </div>
</div>
<div class="pagination">
    {{$items->appends(request()->except('page'))->links('vendor.pagination.default')}}
</div>
@endsection