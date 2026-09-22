<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'ショップ')</title>
</head>
<body>
    <header><h1>My Shop</h1></header>

    <main>
        @if (session('success'))
            <p style="color: green;">{{ session('success') }}</p>
        @endif

        @yield('content')
    </main>
</body>
</html>
