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
            <p class="sell-form__error-message">
                @error('item_image')
                {{ $message->first('item_image') }}
                @enderror
            </p>
        </div>

        <div class="sell-detail-form__group">
            <div class="sell-detail-form__group-title">
                <span class="sell-detail-form__label-item">商品の詳細</span>
            </div>
            <div class="categories__input-form">
                <label for="categories" class="sell-form__label">カテゴリー</label>
                <div class="category-buttons">
                    @foreach($categories as $category)
                    <input type="checkbox" id="cat{{ $category->id }}" class="category-checkbox" name="category_ids[]" value="{{ $category->id }}">
                    <label for="cat{{ $category->id }}" class="category-button">{{ $category->content }}</label>
                    @endforeach
                </div>
            </div>
            <div class="condition__input-form">
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
            </div>
        </div>
    </form>
</div>

@endsection