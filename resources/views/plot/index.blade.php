@extends('layouts.app')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
    <div>
        <a href="{{ route('books.show', $book) }}" class="nwp-text-sm">&larr; {{ $book->title }}</a>
        <h1 class="nwp-heading nwp-mt-1">Plot Outline</h1>
    </div>
</div>

<div class="nwp-tabs">
    <a href="{{ route('books.show', $book) }}" class="nwp-tab">Chapters</a>
    <a href="{{ route('characters.index', $book) }}" class="nwp-tab">Characters</a>
    <a href="{{ route('locations.index', $book) }}" class="nwp-tab">Locations</a>
    <a href="{{ route('plot.index', $book) }}" class="nwp-tab nwp-tab--active">Plot</a>
    <a href="{{ route('world.index', $book) }}" class="nwp-tab">World</a>
    <a href="{{ route('statistics.show', $book) }}" class="nwp-tab">Statistics</a>
</div>

@if($plotPoints->isEmpty())
    <div class="nwp-empty-state">
        <div class="nwp-empty-state__icon">📋</div>
        <div class="nwp-empty-state__title">No plot points yet</div>
        <p class="nwp-empty-state__text">Outline your story's plot to keep track of your narrative arc.</p>
    </div>
@else
    <div id="plot-timeline">
        @foreach($plotPoints as $point)
            <div class="nwp-card nwp-mb-2" data-id="{{ $point->id }}" style="border-left: 4px solid {{ $point->color_label ?? 'var(--color-accent)' }};">
                <div style="display:flex; justify-content:space-between; align-items:start;">
                    <div>
                        <h3 style="font-weight:600;">{{ $point->title }}</h3>
                        <div style="display:flex; gap:8px; margin-top:4px;">
                            <span class="nwp-badge">{{ $point->act->label() }}</span>
                            <span class="nwp-badge nwp-badge--muted">{{ $point->status->label() }}</span>
                        </div>
                        @if($point->description)
                            <p class="nwp-text-sm nwp-text-muted nwp-mt-1">{{ Str::limit($point->description, 150) }}</p>
                        @endif
                    </div>
                    <span class="nwp-text-sm nwp-text-muted">#{{ $point->position }}</span>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
