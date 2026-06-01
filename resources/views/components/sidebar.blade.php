<aside class="nwp-sidebar" id="sidebar">
    <div class="nwp-sidebar__logo">
        <a href="{{ route('dashboard') }}">Mi<span>Writer</span></a>
    </div>

    <nav>
        <ul class="nwp-sidebar__nav">
            <li class="nwp-sidebar__nav-item">
                <a href="{{ route('dashboard') }}" class="nwp-sidebar__nav-link {{ request()->routeIs('dashboard') ? 'nwp-sidebar__nav-link--active' : '' }}">
                    Dashboard
                </a>
            </li>
            <li class="nwp-sidebar__nav-item">
                <a href="{{ route('books.index') }}" class="nwp-sidebar__nav-link {{ request()->routeIs('books.*') ? 'nwp-sidebar__nav-link--active' : '' }}">
                    My Books
                </a>
            </li>
            <li class="nwp-sidebar__nav-item">
                <a href="{{ route('books.create') }}" class="nwp-sidebar__nav-link {{ request()->routeIs('books.create') ? 'nwp-sidebar__nav-link--active' : '' }}">
                    + New Book
                </a>
            </li>
        </ul>

        <div style="padding:16px 24px 8px; font-size:11px; text-transform:uppercase; font-weight:700; color:var(--color-text-muted); letter-spacing:1px;">
            Tools
        </div>
        <ul class="nwp-sidebar__nav">
            <li class="nwp-sidebar__nav-item">
                <a href="{{ route('settings') }}" class="nwp-sidebar__nav-link {{ request()->routeIs('settings*') ? 'nwp-sidebar__nav-link--active' : '' }}">
                    Settings
                </a>
            </li>
        </ul>
    </nav>

    <div style="position: absolute; bottom: 24px; left: 0; right: 0; padding: 0 24px; border-top: 1px solid var(--color-border-light); padding-top: 16px;">
        <div style="display:flex; gap:8px; margin-bottom:12px; align-items:center;">
            <button class="nwp-theme-toggle" id="theme-toggle" onclick="toggleDarkMode()" title="Toggle dark mode">🌙</button>
            <span class="nwp-text-sm nwp-text-muted" id="theme-label">Dark Mode</span>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="nwp-btn nwp-btn--secondary nwp-btn--sm nwp-btn--block">
                Logout
            </button>
        </form>
    </div>
</aside>
