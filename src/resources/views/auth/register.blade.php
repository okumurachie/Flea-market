@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
@endsection

@section('content')
<div class="register-form">
    <h2 class="register-form__heading">会員登録</h2>
    <div class="register-form__inner">
        <form class="register-form__form" action="/register" method="post">
            @csrf
            <div class="register-form__group">
                <label for="name" class="register-form__label">ユーザー名</label>
                <input type="text" class="register-form__input" name="name" id="name" value="{{ old('name') }}">
                <p class="register-form__error-message">
                    @error('name')
                    {{ $message }}
                    @enderror
                </p>
            </div>
            <div class="register-form__group">
                <label for="email" class="register-form__label">メールアドレス</label>
                <input type="mail" class="register-form__input" name="email" id="email" value="{{ old('email') }}">
                <p class="register-form__error-message">
                    @error('email')
                    {{ $message }}
                    @enderror
                </p>
            </div>
            <div class="register-form__group">
                <label for="password" class="register-form__label">パスワード</label>
                <input type="password" class="register-form__input" name="password" id="password" value="{{ old('password') }}">
                <p class="register-form__error-message">
                    @error('password')
                    {{ $message }}
                    @enderror
                </p>
            </div>
            <div class="register-form__group">
                <label for="password" class="register-form__label">確認用パスワード</label>
                <input type="password" class="register-form__input" name="password_confirmation" id="password" />
            </div>
            <input type="submit" class="register-form__btn btn" value="登録する">
        </form>
    </div>
    <a href="/login" class="login-form">ログインはこちら</a>
</div>
@endsection