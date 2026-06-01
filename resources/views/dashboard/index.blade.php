@extends('layouts.app')

@section('content')
<h1 class="nwp-heading nwp-mb-3">Dashboard</h1>

<div class="nwp-stats">
    <div class="nwp-stat">
        <div class="nwp-stat__value">{{ number_format($wordsToday) }}</div>
        <div class="nwp-stat__label">Words Today</div>
    </div>
</div>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
    <h2 class="nwp-heading">Your Books</h2>
    <a href="{{ route('books.create') }}" class="nwp-btn nwp-btn--sm">+ New Book</a>
</div>

@if($books->isEmpty())
    <div class="nwp-empty-state">
        <div class="nwp-empty-state__icon">📚</div>
        <div class="nwp-empty-state__title">No books yet</div>
        <p class="nwp-empty-state__text">Create your first book to get started.</p>
        <a href="{{ route('books.create') }}" class="nwp-btn">Create Book</a>
    </div>
@else
    <div class="nwp-book-grid">
        @foreach($books as $book)
            <a href="{{ route('books.show', $book) }}" class="nwp-book-card">
                <div class="nwp-book-card__cover">
                    @if($book->cover_thumbnail_path)
                        <img src="{{ Storage::url($book->cover_thumbnail_path) }}" alt="{{ $book->title }}">
                    @else
                        📖
                    @endif
                </div>
                <div class="nwp-book-card__info">
                    <div class="nwp-book-card__title">{{ $book->title }}</div>
                    <div class="nwp-book-card__meta">
                        <span class="nwp-badge">{{ $book->status->label() }}</span>
                        <span>{{ number_format($book->total_word_count) }} words</span>
                        <span>{{ $book->updated_at->diffForHumans() }}</span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
@endif

@if($recentActivity->isNotEmpty())
    <h2 class="nwp-heading nwp-mt-4 nwp-mb-2">Recent Activity</h2>
    <div class="nwp-card">
        @foreach($recentActivity as $item)
            <div style="padding:8px 0; border-bottom:1px solid var(--color-border-light);">
                <strong>{{ $item['name'] }}</strong>
                <span class="nwp-text-sm nwp-text-muted">
                    ({{ $item['type'] }}) in {{ $item['book_title'] }}
                    — {{ $item['updated_at']->diffForHumans() }}
                </span>
            </div>
        @endforeach
    </div>
@endif
@endsection
