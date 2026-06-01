<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script>
        (function() {
            if (localStorage.getItem('theme') === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
</head>
<body>
    <div class="nwp-guest">
        <div class="nwp-guest__container">
            <div class="nwp-guest__title">
                Mi<span class="nwp-accent">Writer</span>
            </div>
            <p class="nwp-guest__subtitle">@yield('subtitle', 'Your writing companion')</p>

            <div class="nwp-card">
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
