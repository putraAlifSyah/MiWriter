@extends('layouts.app')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:28px;">
    <div>
        <h1 class="nwp-heading" style="font-size:var(--font-size-2xl);">Dashboard</h1>
        <p class="nwp-text-muted nwp-text-sm" style="margin-top:4px;">Welcome back. Here's your writing overview.</p>
    </div>
    <a href="{{ route('books.create') }}" class="nwp-btn">+ New Book</a>
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
        <div class="nwp-stat__value">{{ $currentStreak }}d</div>
        <div class="nwp-stat__label">Streak</div>
    </div>
    <div class="nwp-stat">
        <div class="nwp-stat__value">{{ number_format($wordsToday) }}</div>
        <div class="nwp-stat__label">Today</div>
    </div>
</div>

<!-- Progress + Chart Row -->
<div class="nwp-dashboard-grid">
    <!-- Writing Progress -->
    <div class="nwp-card">
        <h3 style="font-size:var(--font-size-sm); font-weight:600; color:var(--color-text-muted); margin-bottom:16px;">Writing Progress</h3>

        @if($dailyProgress)
            <div style="margin-bottom:14px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                    <span class="nwp-text-sm" style="font-weight:500;">Daily</span>
                    <span class="nwp-text-sm" style="font-weight:600; color:var(--color-accent);">{{ $dailyProgress['percentage'] }}%</span>
                </div>
                <div class="nwp-progress">
                    <div class="nwp-progress__bar {{ $dailyProgress['met'] ? 'nwp-progress__bar--complete' : '' }}" style="width:{{ $dailyProgress['percentage'] }}%"></div>
                </div>
                <div class="nwp-text-sm nwp-text-muted" style="margin-top:4px;">{{ number_format($dailyProgress['current']) }} / {{ number_format($dailyProgress['target']) }} words</div>
            </div>
        @endif

        @if($weeklyProgress)
            <div style="margin-bottom:14px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                    <span class="nwp-text-sm" style="font-weight:500;">Weekly</span>
                    <span class="nwp-text-sm" style="font-weight:600; color:var(--color-accent);">{{ $weeklyProgress['percentage'] }}%</span>
                </div>
                <div class="nwp-progress">
                    <div class="nwp-progress__bar {{ $weeklyProgress['met'] ? 'nwp-progress__bar--complete' : '' }}" style="width:{{ $weeklyProgress['percentage'] }}%"></div>
                </div>
                <div class="nwp-text-sm nwp-text-muted" style="margin-top:4px;">{{ number_format($weeklyProgress['current']) }} / {{ number_format($weeklyProgress['target']) }} words</div>
            </div>
        @endif

        @if(!$dailyProgress && !$weeklyProgress)
            <p class="nwp-text-sm nwp-text-muted">No writing targets set. Open a book to set your daily or weekly goal.</p>
        @endif

        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; margin-top:16px; padding-top:14px; border-top:1px solid var(--color-border-light);">
            <div>
                <div style="font-size:var(--font-size-xs); color:var(--color-text-muted);">Avg/day</div>
                <div style="font-weight:600; font-size:var(--font-size-sm);">{{ number_format($averageDaily) }}</div>
            </div>
            <div>
                <div style="font-size:var(--font-size-xs); color:var(--color-text-muted);">Best streak</div>
                <div style="font-weight:600; font-size:var(--font-size-sm);">{{ $longestStreak }}d</div>
            </div>
            <div>
                <div style="font-size:var(--font-size-xs); color:var(--color-text-muted);">This week</div>
                <div style="font-weight:600; font-size:var(--font-size-sm);">{{ number_format($wordsThisWeek) }}</div>
            </div>
        </div>
    </div>

    <!-- Last 7 Days -->
    <div class="nwp-card">
        <h3 style="font-size:var(--font-size-sm); font-weight:600; color:var(--color-text-muted); margin-bottom:16px;">Last 7 Days</h3>
        @php
            $maxCount = max(array_column($last7Days, 'count')) ?: 1;
        @endphp
        <div style="display:flex; align-items:flex-end; gap:6px; height:130px;">
            @foreach($last7Days as $day)
                @php
                    $height = $maxCount > 0 ? max(6, ($day['count'] / $maxCount) * 100) : 6;
                @endphp
                <div style="flex:1; display:flex; flex-direction:column; align-items:center; gap:6px; height:100%;">
                    <div style="flex:1; width:100%; display:flex; align-items:flex-end;">
                        <div style="width:100%; background:{{ $day['count'] > 0 ? 'var(--color-accent)' : 'var(--color-bg-tertiary)' }}; height:{{ $height }}%; border-radius:4px; transition:height 0.4s ease; opacity:{{ $day['count'] > 0 ? '1' : '0.5' }};"></div>
                    </div>
                    <span style="font-size:10px; color:var(--color-text-muted); font-weight:500;">{{ $day['day'] }}</span>
                </div>
            @endforeach
        </div>
        <div style="margin-top:12px; padding-top:12px; border-top:1px solid var(--color-border-light); text-align:center;">
            <span class="nwp-text-sm nwp-text-muted">
                Peak: <strong style="color:var(--color-text-primary);">{{ number_format(max(array_column($last7Days, 'count'))) }}</strong> words
            </span>
        </div>
    </div>
</div>

<!-- Books -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
    <h2 class="nwp-heading" style="font-size:var(--font-size-lg);">Your Books</h2>
    @if($books->count() > 6)
        <a href="{{ route('books.index') }}" class="nwp-text-sm" style="font-weight:500;">View all</a>
    @endif
</div>

@if($books->isEmpty())
    <div class="nwp-card" style="text-align:center; padding:48px;">
        <div class="nwp-empty-state__title">Start your first project</div>
        <p class="nwp-empty-state__text">Create a book to begin writing.</p>
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
                        <span style="opacity:0.3;">{{ strtoupper(mb_substr($book->title, 0, 2)) }}</span>
                    @endif
                </div>
                <div class="nwp-book-card__info">
                    <div class="nwp-book-card__title">{{ $book->title }}</div>
                    <div class="nwp-book-card__meta">
                        <span class="nwp-badge">{{ $book->status->label() }}</span>
                        <span>{{ number_format($book->total_word_count) }} words</span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
@endif

<!-- Recent Activity -->
@if($recentActivity->isNotEmpty())
    <h2 class="nwp-heading nwp-mb-2" style="font-size:var(--font-size-lg);">Recent Activity</h2>
    <div class="nwp-card" style="padding:0; overflow:hidden;">
        @foreach($recentActivity as $i => $item)
            <a href="{{ $item['url'] }}" style="display:flex; justify-content:space-between; align-items:center; padding:12px 20px; text-decoration:none; color:inherit; transition:background var(--transition); {{ $i < $recentActivity->count() - 1 ? 'border-bottom:1px solid var(--color-border-light);' : '' }}">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="font-weight:500; font-size:var(--font-size-sm);">{{ $item['name'] }}</span>
                    <span class="nwp-badge nwp-badge--muted">{{ $item['type'] }}</span>
                </div>
                <span class="nwp-text-sm nwp-text-muted">{{ $item['updated_at']->diffForHumans() }}</span>
            </a>
        @endforeach
    </div>
@endif
@endsection
