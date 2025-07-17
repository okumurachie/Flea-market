@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/sell.css')}}">
@endsection

@section('content')
<div class="sell-form__content">
    <div class="sell-form__heading">
        <h2>商品の出品</h2>
    </div>
    <form action="/sell" class="sell-form" method="post" enctype="multipart/form-data">
        @csrf
        <div class="sell-image-form__group">
            <div class="sell-image-form__group-title">
                <p class="sell-image-form__label-item">商品画像</p>
            </div>
            <div class="sell-form__image__input">
                <div class="preview-wrapper">
                    <img id="preview" class="image-preview" src="" alt="商品画像" style="display: none;">
                    <label for="imageInput" class="sell-file-label">画像を選択する</label>
                    <input type="file" id="imageInput" accept="image/*" name="item_image" class="visually-hidden">
                    <p class="sell-form__error-message">
                        @error('item_image')
                        {{ $message}}
                        @enderror
                    </p>
                </div>
                <script>
                    const input = document.getElementById('imageInput');
                    const preview = document.getElementById('preview')

                    input.addEventListener('change', function() {
                        const file = input.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                preview.src = e.target.result;
                                preview.style.display = 'block';
                            };
                            reader.readAsDataURL(file);
                        }
                    });
                </script>
            </div>
        </div>

        <div class="sell-form__group">
            <div class="sell-form__group-title">
                <span class="sell-form__label-item">商品の詳細</span>
            </div>
            <div class="input-form">
                <label for="categories" class="sell-form__label">カテゴリー</label>
                <div class="category-buttons">
                    @foreach($categories as $category)
                    <input
                        type="checkbox" id="cat{{ $category->id }}" class="category-checkbox" name="category_ids[]" value="{{ $category->id }}"
                        @if(is_array(old('category_ids')) && in_array($category->id, old('category_ids'))) checked @endif
                    >
                    <label for="cat{{ $category->id }}" class="category-button">{{ $category->content }}</label>
                    @endforeach
                </div>
                <p class="sell-form__error-message">
                    @error('category_ids')
                    {{ $message }}
                    @enderror
                </p>
            </div>
            <div class="input-form">
                <label for="condition" class="sell-form__label">商品の状態</label>
                <div class="condition__select-inner">
                    <select name="condition_id" class="select-condition">
                        <option disabled selected>選択してください</option>
                        @foreach($conditions as $condition)
                        <option value="{{ $condition->id }}" {{ old('condition_id')==$condition ->id ? 'selected' : '' }}>
                            {{$condition->condition }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <p class="sell-form__error-message">
                    @error('condition_id')
                    {{ $message }}
                    @enderror
                </p>
            </div>
        </div>
        <div class="sell-form__group">
            <div class="sell-form__group-title">
                <span class="sell-form__label-item">商品名と説明</span>
            </div>
            <div class="input-form">
                <label for="item_name" class="sell-form__label">商品名</label>
                <input class="input" type="text" name="item_name" value="{{old('item_name')}}">
                <p class="sell-form__error-message">
                    @error('item_name')
                    {{ $message }}
                    @enderror
                </p>
            </div>
            <div class="input-form">
                <label for="brand" class="sell-form__label">ブランド</label>
                <input class="input" type="text" name="brand" value="{{old('brand')}}">
            </div>
            <div class="input-form">
                <label for="item-description" class="sell-form__label">商品の説明</label>
                <textarea class="textarea" name="description" id="" cols="30" rows="5">{{ old('description') }}</textarea>
                <p class="sell-form__error-message">
                    @error('description')
                    {{ $message }}
                    @enderror
                </p>
            </div>
            <div class="input-form">
                <label for="price" class="sell-form__label">販売価格</label>
                <div class="input-price">
                    <span class="input-price-symbol">¥</span>
                    <input class="input" type="text" name="price" value="{{old('price')}}">
                </div>
                <p class="sell-form__error-message">
                    @error('price')
                    {{ $message }}
                    @enderror
                </p>
            </div>
        </div>
        <input type="submit" class="sell-form__btn" value="出品する">
    </form>
</div>

@endsection