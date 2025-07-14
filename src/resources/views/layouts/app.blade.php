<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FleaMarket</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/sanitize.css')}}">
    <link rel="stylesheet" href="{{ asset('css/common.css')}}">
    @yield('css')
</head>

<body>
    @if (session('message'))
    <div class="alert alert-success">
        {{ session('message') }}
    </div>
    @endif
    <div class="app">
        <header class="header">
            <div class="header_logo__group">
                <a href="{{ route('home') }}" class="go-to-home">
                    <img src="{{asset('images/logo.svg')}}" alt="header_logo" class="header_logo">
                </a>
            </div>
            @if (!Request::is('login') && !Request::is('register') && !Request::is('email/verify'))
            <form action="/" class="search_field" method="get">
                <input type="hidden" name="tab" value="{{ request('tab') }}">
                <input type="text" class="search-form__keyword-input" name="keyword" placeholder="なにをお探しですか？" value="{{request('keyword')}}">
            </form>
            <div class="header__link">
                @if (Auth::check())
                <form class="form" action="/logout" method="post">
                    @csrf
                    <button class="header-nav__button">ログアウト</button>
                </form>
                @else
                <a href="/login" class="for-login-form">ログイン</a>
                @endif
                <a href="/mypage" class="mypage">マイページ</a>
                <a href="" class="put-up_items">出品</a>
            </div>
            @endif
        </header>
        <div class="content">
            @yield('content')
        </div>
    </div>

</body>

</html>