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
    @stack('styles')
</head>
<body>
    <div class="nwp-layout">
        <button class="nwp-sidebar-toggle" id="sidebar-toggle" aria-label="Toggle navigation">☰</button>

        @include('components.sidebar')

        <div class="nwp-sidebar-overlay" id="sidebar-overlay"></div>

        <main class="nwp-main">
            @if(session('success'))
                <div class="nwp-toast nwp-toast--success" style="position:relative; top:0; right:0; margin-bottom:16px;">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="nwp-toast nwp-toast--error" style="position:relative; top:0; right:0; margin-bottom:16px;">
                    {{ session('error') }}
                </div>
            @endif

            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>

    <div class="nwp-toast-container" id="toast-container"></div>

    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
