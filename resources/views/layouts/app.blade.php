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
            document.documentElement.setAttribute('data-lang', localStorage.getItem('miwriter-lang') || 'en');
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

    <!-- AI Chat Widget -->
    <div id="ai-widget" class="ai-widget">
        <button id="ai-toggle" class="ai-widget__toggle" onclick="AiChat.toggle()" title="Ask AI">AI</button>
        <div id="ai-panel" class="ai-widget__panel" style="display:none;">
            <div class="ai-widget__header">
                <span style="font-weight:600; font-size:var(--font-size-sm);">AI Assistant</span>
                <button onclick="AiChat.toggle()" class="ai-widget__close">&times;</button>
            </div>
            <div id="ai-messages" class="ai-widget__messages"></div>
            <div class="ai-widget__input">
                <select id="ai-book-select" class="ai-widget__select">
                    <option value="">All books context</option>
                    @auth
                        @foreach(auth()->user()->books()->orderByDesc('updated_at')->get() as $b)
                            <option value="{{ $b->id }}">{{ $b->title }}</option>
                        @endforeach
                    @endauth
                </select>
                <form id="ai-form" onsubmit="AiChat.send(event)" style="display:flex; gap:6px;">
                    <input type="text" id="ai-input" placeholder="Ask about your story..." autocomplete="off" style="flex:1;">
                    <button type="submit" id="ai-send-btn">Send</button>
                </form>
            </div>
            @auth
                @if(!auth()->user()->ai_provider)
                <div class="ai-widget__setup">
                    <p style="font-size:var(--font-size-xs); color:var(--color-text-muted); margin-bottom:8px;">Set up your AI provider in <a href="{{ route('settings') }}">Settings</a> to start chatting.</p>
                </div>
                @endif
            @endauth
        </div>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
