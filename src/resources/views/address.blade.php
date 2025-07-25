@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/address.css') }}">
@endsection

@section('content')
<div class="address-form">
    <h2 class="address-form__heading">住所の変更</h2>
    <div class="address-form__inner">
        <form class="address-form__form" action="{{route('address.update', ['id' => $item->id])}}" method="post">
            @method('patch')
            @csrf
            <div class="address-form__group">
                <label for="post_code" class="address-form__label">郵便番号</label>
                <input type="text" class="address-form__input" name="post_code" id="post_code" maxlength="8" value="{{ old('post_code', $profile->post_code ?? '' ) }}">
                <p class="address-form__error-message">
                    @error('post_code')
                    {{ $message }}
                    @enderror
                </p>
            </div>
            <div class="address-form__group">
                <label for="address" class="address-form__label">住所</label>
                <input type="text" class="address-form__input" name="address" id="address" value="{{ old('address', $profile->address ?? '') }}">
                <p class="address-form__error-message">
                    @error('address')
                    {{ $message }}
                    @enderror
                </p>
            </div>
            <div class="address-form__group">
                <label for="building" class="address-form__label">建物名</label>
                <input type="text" class="address-form__input" name="building" id="building" value="{{ old('building', $profile->building ?? '') }}">
            </div>
            <input type="submit" class="address-form__btn btn" value="更新する">
        </form>
    </div>
</div>
@endsection