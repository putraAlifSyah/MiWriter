@extends('layouts.app')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
    <h1 class="nwp-heading">Dashboard</h1>
    <a href="{{ route('books.create') }}" class="nwp-btn nwp-btn--sm">+ New Book</a>
</div>

<!-- Overview Stats -->
<div class="nwp-stats">
    <div class="nwp-stat">
        <div class="nwp-stat__value">{{ number_format($totalWords) }}</div>
        <div class="nwp-stat__label">Total Words</div>
    </div>
    <div class="nwp-stat">
        <div class="nwp-stat__value">{{ $totalBooks }}</div>
        <div class="nwp-stat__label">Books</div>
    </div>
    <div class="nwp-stat">
        <div class="nwp-stat__value">{{ $totalChapters }}</div>
        <div class="nwp-stat__label">Chapters</div>
    </div>
    <div class="nwp-stat">
        <div class="nwp-stat__value">{{ $totalCharacters }}</div>
        <div class="nwp-stat__label">Characters</div>
    </div>
    <div class="nwp-stat">
        <div class="nwp-stat__value">{{ $currentStreak }}</div>
        <div class="nwp-stat__label">Day Streak</div>
    </div>
    <div class="nwp-stat">
        <div class="nwp-stat__value">{{ number_format($wordsToday) }}</div>
        <div class="nwp-stat__label">Words Today</div>
    </div>
</div>

<!-- Writing Progress & Weekly Chart -->
<div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:32px;">
    <!-- Daily/Weekly Progress -->
    <div class="nwp-card">
        <h3 class="nwp-heading nwp-mb-2" style="font-size:var(--font-size-sm);">Writing Progress</h3>

        @if($dailyProgress)
            <div class="nwp-mb-2">
                <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                    <span class="nwp-text-sm">Daily: {{ number_format($dailyProgress['current']) }} / {{ number_format($dailyProgress['target']) }}</span>
                    <span class="nwp-text-sm nwp-accent">{{ $dailyProgress['percentage'] }}%</span>
                </div>
                <div class="nwp-progress">
                    <div class="nwp-progress__bar {{ $dailyProgress['met'] ? 'nwp-progress__bar--complete' : '' }}" style="width:{{ $dailyProgress['percentage'] }}%"></div>
                </div>
            </div>
        @endif

        @if($weeklyProgress)
            <div class="nwp-mb-2">
                <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                    <span class="nwp-text-sm">Weekly: {{ number_format($weeklyProgress['current']) }} / {{ number_format($weeklyProgress['target']) }}</span>
                    <span class="nwp-text-sm nwp-accent">{{ $weeklyProgress['percentage'] }}%</span>
                </div>
                <div class="nwp-progress">
                    <div class="nwp-progress__bar {{ $weeklyProgress['met'] ? 'nwp-progress__bar--complete' : '' }}" style="width:{{ $weeklyProgress['percentage'] }}%"></div>
                </div>
            </div>
        @endif

        @if(!$dailyProgress && !$weeklyProgress)
            <p class="nwp-text-sm nwp-text-muted">No writing targets set yet. Open a book and set your daily or weekly goal.</p>
        @endif

        <div style="margin-top:16px; padding-top:12px; border-top:1px solid var(--color-border-light);">
            <div style="display:flex; justify-content:space-between;">
                <div>
                    <div class="nwp-text-sm nwp-text-muted">Avg. daily (30d)</div>
                    <div style="font-weight:600;">{{ number_format($averageDaily) }} words</div>
                </div>
                <div>
                    <div class="nwp-text-sm nwp-text-muted">Longest streak</div>
                    <div style="font-weight:600;">{{ $longestStreak }} days</div>
                </div>
                <div>
                    <div class="nwp-text-sm nwp-text-muted">This week</div>
                    <div style="font-weight:600;">{{ number_format($wordsThisWeek) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Last 7 Days Mini Chart -->
    <div class="nwp-card">
        <h3 class="nwp-heading nwp-mb-2" style="font-size:var(--font-size-sm);">Last 7 Days</h3>
        @php
            $maxCount = max(array_column($last7Days, 'count')) ?: 1;
        @endphp
        <div style="display:flex; align-items:flex-end; gap:8px; height:120px; padding-top:8px;">
            @foreach($last7Days as $day)
                @php
                    $height = $maxCount > 0 ? max(4, ($day['count'] / $maxCount) * 100) : 4;
                @endphp
                <div style="flex:1; display:flex; flex-direction:column; align-items:center; gap:4px;">
                    <div style="width:100%; background:var(--color-accent); height:{{ $height }}%; min-height:4px; transition:height 0.3s ease; opacity:{{ $day['count'] > 0 ? '1' : '0.2' }};"></div>
                    <span style="font-size:10px; color:var(--color-text-muted); font-weight:600;">{{ $day['day'] }}</span>
                </div>
            @endforeach
        </div>
        <div style="margin-top:8px; text-align:center;">
            <span class="nwp-text-sm nwp-text-muted">
                Peak: {{ number_format(max(array_column($last7Days, 'count'))) }} words
            </span>
        </div>
    </div>
</div>

<!-- Books Section -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
    <h2 class="nwp-heading">Your Books</h2>
    <a href="{{ route('books.index') }}" class="nwp-text-sm">View all</a>
</div>

@if($books->isEmpty())
    <div class="nwp-empty-state">
        <div class="nwp-empty-state__title">No books yet</div>
        <p class="nwp-empty-state__text">Start your first writing project.</p>
        <a href="{{ route('books.create') }}" class="nwp-btn">Create Book</a>
    </div>
@else
    <div class="nwp-book-grid nwp-mb-4">
        @foreach($books->take(6) as $book)
            <a href="{{ route('books.show', $book) }}" class="nwp-book-card">
                <div class="nwp-book-card__cover">
                    @if($book->cover_thumbnail_path)
                        <img src="{{ Storage::url($book->cover_thumbnail_path) }}" alt="{{ $book->title }}">
                    @else
                        <span style="font-size:2rem; opacity:0.3;">{{ strtoupper(substr($book->title, 0, 2)) }}</span>
                    @endif
                </div>
                <div class="nwp-book-card__info">
                    <div class="nwp-book-card__title">{{ $book->title }}</div>
                    <div class="nwp-book-card__meta">
                        <span class="nwp-badge">{{ $book->status->label() }}</span>
                        <span>{{ number_format($book->total_word_count) }} words</span>
                    </div>
                    <div class="nwp-text-sm nwp-text-muted" style="margin-top:4px;">
                        {{ $book->updated_at->diffForHumans() }}
                    </div>
                </div>
            </a>
        @endforeach
    </div>
@endif

<!-- Recent Activity -->
@if($recentActivity->isNotEmpty())
    <h2 class="nwp-heading nwp-mb-2">Recent Activity</h2>
    <div class="nwp-card">
        @foreach($recentActivity as $item)
            <a href="{{ $item['url'] }}" style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid var(--color-border-light); text-decoration:none; color:inherit;">
                <div>
                    <span style="font-weight:500;">{{ $item['name'] }}</span>
                    <span class="nwp-badge nwp-badge--muted" style="margin-left:8px; font-size:10px;">{{ $item['type'] }}</span>
                </div>
                <div class="nwp-text-sm nwp-text-muted" style="white-space:nowrap; margin-left:16px;">
                    {{ $item['book_title'] }} &middot; {{ $item['updated_at']->diffForHumans() }}
                </div>
            </a>
        @endforeach
    </div>
@endif
@endsection
