<aside class="nwp-sidebar" id="sidebar">
    <div class="nwp-sidebar__logo">
        <a href="{{ route('dashboard') }}">Mi<span>Writer</span></a>
    </div>

    <nav style="flex:1;">
        <div style="padding:0 8px 6px 20px; font-size:10px; font-weight:600; color:var(--color-text-muted); letter-spacing:0.05em; text-transform:uppercase;">Menu</div>
        <ul class="nwp-sidebar__nav">
            <li class="nwp-sidebar__nav-item">
                <a href="{{ route('dashboard') }}" class="nwp-sidebar__nav-link {{ request()->routeIs('dashboard') ? 'nwp-sidebar__nav-link--active' : '' }}">
                    Dashboard
                </a>
            </li>
            <li class="nwp-sidebar__nav-item">
                <a href="{{ route('books.index') }}" class="nwp-sidebar__nav-link {{ request()->routeIs('books.*') && !request()->routeIs('books.create') ? 'nwp-sidebar__nav-link--active' : '' }}">
                    My Books
                </a>
            </li>
            <li class="nwp-sidebar__nav-item">
                <a href="{{ route('books.create') }}" class="nwp-sidebar__nav-link {{ request()->routeIs('books.create') ? 'nwp-sidebar__nav-link--active' : '' }}">
                    New Book
                </a>
            </li>
        </ul>

        <div style="padding:16px 8px 6px 20px; font-size:10px; font-weight:600; color:var(--color-text-muted); letter-spacing:0.05em; text-transform:uppercase;">Account</div>
        <ul class="nwp-sidebar__nav">
            <li class="nwp-sidebar__nav-item">
                <a href="{{ route('guide') }}" class="nwp-sidebar__nav-link {{ request()->routeIs('guide') ? 'nwp-sidebar__nav-link--active' : '' }}">
                    Guide
                </a>
            </li>
            <li class="nwp-sidebar__nav-item">
                <a href="{{ route('settings') }}" class="nwp-sidebar__nav-link {{ request()->routeIs('settings*') ? 'nwp-sidebar__nav-link--active' : '' }}">
                    Settings
                </a>
            </li>
        </ul>
    </nav>

    <div style="padding: 12px 12px 0; border-top: 1px solid var(--color-border-light); margin-top:auto;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
            <span class="nwp-text-sm" style="font-weight:500; color:var(--color-text-secondary);">{{ Auth::user()->name }}</span>
            <div style="display:flex; gap:4px;">
                <button class="nwp-theme-toggle" id="lang-toggle-sidebar" onclick="toggleLang()" title="Toggle language" style="font-size:12px; width:32px; height:32px;">EN</button>
                <button class="nwp-theme-toggle" id="theme-toggle" onclick="toggleDarkMode()" title="Toggle theme" style="width:32px; height:32px;">🌙</button>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="nwp-btn nwp-btn--secondary nwp-btn--sm nwp-btn--block">
                Log out
            </button>
        </form>
    </div>
</aside>
