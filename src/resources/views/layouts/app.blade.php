<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FleaMarket</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/sanitize.css')}}">
    <link rel="stylesheet" href="{{ asset('css/common.css')}}">
    @yield('css')
</head>

<body>
    <div class="app">
        <header class="header">
            <img src="{{asset('images/logo.svg')}}" alt="header_logo" class="header_logo">
            @if (!Request::is('login') && !Request::is('register') && !Request::is('email/verify'))
            <form action="" class="search_field">
                @csrf
                <input type="text" class="search-form__keyword-input" type="text" name="keyword" placeholder="なにをお探しですか？" value="{{request('keyword')}}">
            </form>
            <div class="header__link">
                <a href="" class="login">ログイン</a>
                <a href="" class="mypage">マイページ</a>
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