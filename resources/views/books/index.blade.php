@extends('layouts.app')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
    <h1 class="nwp-heading">All Books</h1>
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
@endsection
