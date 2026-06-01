@extends('layouts.app')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
    <div>
        <a href="{{ route('books.show', $book) }}" class="nwp-text-sm">&larr; {{ $book->title }}</a>
        <h1 class="nwp-heading nwp-mt-1">Locations</h1>
    </div>
</div>

<div class="nwp-tabs">
    <a href="{{ route('books.show', $book) }}" class="nwp-tab">Chapters</a>
    <a href="{{ route('characters.index', $book) }}" class="nwp-tab">Characters</a>
    <a href="{{ route('locations.index', $book) }}" class="nwp-tab nwp-tab--active">Locations</a>
    <a href="{{ route('plot.index', $book) }}" class="nwp-tab">Plot</a>
    <a href="{{ route('world.index', $book) }}" class="nwp-tab">World</a>
    <a href="{{ route('statistics.show', $book) }}" class="nwp-tab">Statistics</a>
</div>

<div style="display:flex; gap:12px; margin-bottom:24px; flex-wrap:wrap;">
    <form method="GET" style="display:flex; gap:12px; flex:1; min-width:200px;">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search locations..." class="nwp-input" style="flex:1;">
        <select name="type" class="nwp-select" style="width:auto; min-width:140px;" onchange="this.form.submit()">
            <option value="">All Types</option>
            @foreach(\App\Enums\LocationType::cases() as $type)
                <option value="{{ $type->value }}" {{ request('type') === $type->value ? 'selected' : '' }}>{{ $type->label() }}</option>
            @endforeach
        </select>
    </form>
</div>

@if($locations->isEmpty())
    <div class="nwp-empty-state">
        <div class="nwp-empty-state__icon">🗺️</div>
        <div class="nwp-empty-state__title">No locations yet</div>
        <p class="nwp-empty-state__text">Add locations to build your story's world.</p>
    </div>
@else
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:16px;">
        @foreach($locations as $location)
            <div class="nwp-card">
                <h3 style="font-weight:600;">{{ $location->name }}</h3>
                <span class="nwp-badge nwp-mt-1">{{ $location->type->label() }}</span>
                @if($location->description)
                    <p class="nwp-text-sm nwp-text-muted nwp-mt-1">{{ Str::limit($location->description, 100) }}</p>
                @endif
            </div>
        @endforeach
    </div>
@endif
@endsection
