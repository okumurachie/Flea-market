@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/purchase.css') }}">
@endsection

@section('content')
<div class="purchase-form__content">
    <form action="{{ route('purchase.checkout') }}" class="purchase-form" method="post">
        @csrf
        <input type="hidden" name="item_id" value="{{ $item['id'] }}">
        <div class="purchase-form__input">
            <div class="image-input__group">
                <div class="item__img">
                    <img src="{{asset($item['item_image'])}}" alt="{{$item['item_image']}}">
                </div>
                <div class="item__information">
                    <h3 class="item__name">{{$item['item_name']}}</h3>
                    <h3 class="item__price">
                        <span class="yen-mark1">¥</span>
                        <span class="price-of-item1">{{ number_format($item['price']) }}</span>
                    </h3>
                </div>
            </div>
            <div class="input__group">
                <h4>支払い方法</h4>
                <div class="payment__select">
                    <select name="payment_method" class="payment__select-inner" id="payment_method">
                        <option disabled selected>選択してください</option>
                        <option value="konbini" {{ old('payment_method') == 'konbini' ? 'selected' : '' }}>コンビニ支払い</option>
                        <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>カード支払い</option>
                    </select>
                </div>
                <p class="purchase-form__error-message">
                    @error('payment_method')
                    {{ $message }}
                    @enderror
                </p>
            </div>
            <div class="input__group">
                <div class="input-title__group">
                    <h4>配送先</h4>
                    <a href="/purchase/address/{{$item['id']}}" class="edit-address">変更する</a>
                </div>
                <div class="destination__group">
                    <div class="postal-wrapper">
                        <span class="postal-mark">〒</span>
                        <input type="text" class="post_code__input" name="post_code" id="post_code" maxlength="8" value="{{ old('post_code', $user->profile->post_code ?? '') }}">
                    </div>
                    <input type="text" class="destination__input" name="destination" value="{{old('destination', $user->profile->address . ($user->profile->building ?? '')) }}">
                </div>
                <p class="purchase-form__error-message">
                    @error('post_code')
                    {{ $message }}
                    @enderror
                </p>
                <p class="purchase-form__error-message">
                    @error('destination')
                    {{ $message }}
                    @enderror
                </p>
            </div>
        </div>
        <div class="purchase-form__confirmed">
            <table class="confirmed-table">
                <tr class="table-row">
                    <th class="table-th">商品代金</th>
                    <td class="table-td-price">
                        <span class="yen-mark2">¥</span>
                        <span class="price-of-item2">{{ number_format($item['price'])}}</span>
                    </td>
                </tr>
                <tr class="table-row">
                    @php
                    $rawValue = old('payment_method') ?? '';
                    $paymentMethodLabel = [
                    'konbini' => 'コンビニ支払い',
                    'card' => 'カード支払い',
                    ][$rawValue] ?? '選択してください';
                    @endphp
                    <th class="table-th">支払い方法</th>
                    <td class="table-td-payment" id="payment-method-display">{{ $paymentMethodLabel}}</td>
                </tr>
            </table>
            <div class="purchase-form__button">
                <button class="purchase__button">購入する</button>
            </div>
        </div>
    </form>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const select = document.getElementById('payment_method');
        const display = document.getElementById('payment-method-display');

        const labels = {
            'konbini': 'コンビニ支払い',
            'card': 'カード支払い',
            '': '選択してください',
        };

        function updateDisplay() {
            const value = select.value;
            if (!value) {
                display.textContent = '選択してください';
            } else {
                display.textContent = labels[value] ?? '選択してください';
            }
        }
        updateDisplay();

        select.addEventListener('change', updateDisplay);
    });
</script>
@endsection