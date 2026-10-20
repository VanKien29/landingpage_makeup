<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập quản trị — Văn Cường</title>
    <link rel="icon" href="{{ asset('favicon.png') }}"><link rel="stylesheet" href="{{ asset('css/academy.css') }}"><link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
</head>
<body>
    <main class="admin-login-shell">
        <div class="admin-login-visual" aria-hidden="true"><div class="admin-login-caption"><p>VĂN CƯỜNG MAKEUP & ACADEMY · JAPAN</p><h1>The art of<br><em>education.</em></h1></div></div>
        <section class="admin-login-panel" aria-labelledby="admin-login-title">
            <div class="admin-login-content">
                <a class="admin-login-brand" href="{{ route('home') }}"><img src="{{ asset('images/van-cuong-logo-transparent.png') }}" alt="Văn Cường Makeup & Academy"><span>ACADEMY CONTROL PANEL</span></a>
                <h2 id="admin-login-title">Chào mừng trở lại.</h2><p>Đăng nhập để quản lý nội dung học viện và workshop.</p>
                <form class="admin-login-form" method="POST" action="{{ route('login.store') }}">
                    @csrf
                    <div class="admin-field"><label for="email">Email quản trị</label><input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus></div>
                    <div class="admin-field"><label for="password">Mật khẩu</label><input type="password" id="password" name="password" autocomplete="current-password" required></div>
                    @if($errors->any())<p class="form-status error" role="alert">{{ $errors->first() }}</p>@endif
                    <button class="admin-button" type="submit">Đăng nhập @include('partials.icon', ['name' => 'arrow-up-right'])</button>
                </form>
                <p class="admin-login-help">Khu vực dành riêng cho quản trị viên được cấp quyền.</p>
            </div>
        </section>
    </main>
</body>
</html>
