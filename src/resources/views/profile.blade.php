@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endsection

@section('content')
<div class="profile-form">
    <h2 class="profile-form__heading">プロフィール設定</h2>
    <div class="profile-content-form">
        <form
            class="profile-form__form"
            action="{{isset($profile) ? route('profile.update', $profile->id) : route('profile/store')}}"
            method="post"
            enctype="multipart/form-data">
            @csrf
            @if(isset($profile))
            @method('put')
            @endif
            <div class="profile-image-form__group">
                <div class="product-form__image__input">
                    @if(empty($profile->image))
                    <img id="preview" src="{{ asset('images/default.png') }}" alt="デフォルト画像">
                    @else
                    <img id="preview" src="{{ asset('storage/' . $profile->image) }}" alt="プロフィール画像">
                    @endif
                    <label for="imageInput" class="file-label">画像を選択する</label>
                    <input type="file" id="imageInput" accept="image/*" name="image" class="visually-hidden">

                    <script>
                        const input = document.getElementById('imageInput');
                        const preview = document.getElementById('preview')

                        input.addEventListener('change', function() {
                            const file = input.files[0];
                            if (file) {
                                const reader = new FileReader();
                                reader.onload = function(e) {
                                    preview.src = e.target.result;
                                };
                                reader.readAsDataURL(file);
                            }
                        });
                    </script>
                </div>
                <p class="profile-form__error-message">
                    @error('image')
                    {{ $message }}
                    @enderror
                </p>
            </div>
            <div class="profile-form__group">
                <label for="name" class="profile-form__label">ユーザー名</label>
                <input type="text" class="profile-form__input" name="name" id="name" value="{{ old('name', $profile->name ?? '') }}">
                <p class="profile-form__error-message">
                    @error('name')
                    {{ $message }}
                    @enderror
                </p>
            </div>
            <div class="profile-form__group">
                <label for="post_code" class="profile-form__label">郵便番号</label>
                <input type="text" class="profile-form__input" name="post_code" id="post_code" pattern="\d{3}-?\d{4}" maxlength="8" placeholder="例：123-4567" value="{{ old('post_code', $profile->post_code ?? '' ) }}">
                <p class="profile-form__error-message">
                    @error('post_code')
                    {{ $message }}
                    @enderror
                </p>
            </div>
            <div class="profile-form__group">
                <label for="address" class="profile-form__label">住所</label>
                <input type="text" class="profile-form__input" name="address" id="address" value="{{ old('address', $profile->address ?? '') }}">
                <p class="profile-form__error-message">
                    @error('address')
                    {{ $message }}
                    @enderror
                </p>
            </div>
            <div class="profile-form__group">
                <label for="building" class="profile-form__label">建物名</label>
                <input type="text" class="profile-form__input" name="building" value="{{ old('building', $profile->building ?? '') }}">
            </div>
            <input type="submit" class="profile-form__btn btn" value="更新する">
        </form>
    </div>
</div>
@endsection