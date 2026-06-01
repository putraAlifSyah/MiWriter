@extends('layouts.app')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
    <div>
        <a href="{{ route('books.show', $book) }}" class="nwp-text-sm">&larr; {{ $book->title }}</a>
        <h1 class="nwp-heading nwp-mt-1">World Building</h1>
    </div>
</div>

<div class="nwp-tabs">
    <a href="{{ route('books.show', $book) }}" class="nwp-tab">Chapters</a>
    <a href="{{ route('characters.index', $book) }}" class="nwp-tab">Characters</a>
    <a href="{{ route('locations.index', $book) }}" class="nwp-tab">Locations</a>
    <a href="{{ route('plot.index', $book) }}" class="nwp-tab">Plot</a>
    <a href="{{ route('world.index', $book) }}" class="nwp-tab nwp-tab--active">World</a>
    <a href="{{ route('statistics.show', $book) }}" class="nwp-tab">Statistics</a>
</div>

@if($grouped->isEmpty())
    <div class="nwp-empty-state">
        <div class="nwp-empty-state__icon">🌍</div>
        <div class="nwp-empty-state__title">No world elements yet</div>
        <p class="nwp-empty-state__text">Document your world's rules, cultures, and systems.</p>
    </div>
@else
    @foreach($grouped as $category => $elements)
        <div class="nwp-mb-3">
            <h2 class="nwp-heading nwp-mb-2" style="font-size:var(--font-size-lg);">
                {{ ucfirst($category) }}
                <span class="nwp-text-sm nwp-text-muted">({{ $elements->count() }})</span>
            </h2>
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:16px;">
                @foreach($elements as $element)
                    <div class="nwp-card">
                        <h3 style="font-weight:600;">{{ $element->name }}</h3>
                        @if($element->description)
                            <p class="nwp-text-sm nwp-text-muted nwp-mt-1">{{ Str::limit($element->description, 120) }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
@endif
@endsection
