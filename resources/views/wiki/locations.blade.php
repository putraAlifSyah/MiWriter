@extends('wiki.layout')

@section('wiki_content')
<div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:32px;">
    <div>
        <h2 class="nwp-heading" style="font-size:32px; margin-bottom:8px;">Locations</h2>
        <p class="nwp-text-muted">The places that make up your world.</p>
    </div>
    
    <form method="GET" action="{{ route('books.wiki.locations', $book) }}" style="display:flex; align-items:center; gap:8px;">
        <label for="type" class="nwp-text-sm nwp-text-muted">Filter Type:</label>
        <select name="type" id="type" class="nwp-input nwp-input--sm" style="width:120px;" onchange="this.form.submit()">
            <option value="all" {{ request('type') == 'all' || !request('type') ? 'selected' : '' }}>All Types</option>
            <option value="city" {{ request('type') == 'city' ? 'selected' : '' }}>City</option>
            <option value="building" {{ request('type') == 'building' ? 'selected' : '' }}>Building</option>
            <option value="landscape" {{ request('type') == 'landscape' ? 'selected' : '' }}>Landscape</option>
            <option value="realm" {{ request('type') == 'realm' ? 'selected' : '' }}>Realm</option>
            <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>Other</option>
        </select>
    </form>
</div>

<section id="locations">
    @if($locations->isEmpty())
        <div class="nwp-text-muted" style="font-style:italic;">No locations found.</div>
    @else
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:20px;">
            @foreach($locations as $loc)
                @php
                    $locDetails = [
                        'Image' => $loc->image_path ? asset('storage/'.$loc->image_path) : null,
                        'Description' => $loc->description,
                        'Atmosphere' => $loc->atmosphere,
                        'Notable Features' => $loc->notable_features,
                        'Notes' => $loc->notes,
                    ];
                    $jsonData = json_encode([
                        'type' => 'Location',
                        'name' => $loc->name,
                        'color' => ['hex' => '#10b981'],
                        'desc' => $loc->type ? (is_object($loc->type) ? $loc->type->label() : $loc->type) : 'Location',
                        'details' => $locDetails
                    ]);
                @endphp
                    <div class="nwp-card" style="display:flex; flex-direction:column; padding:16px; cursor:pointer; transition:transform 0.2s, box-shadow 0.2s;" onclick="window.location.href='{{ route('books.wiki.locations.show', [$book, $loc]) }}'" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px;">
                        @if($loc->image_path)
                            <img src="{{ asset('storage/'.$loc->image_path) }}" style="width:48px; height:48px; border-radius:8px; object-fit:cover;">
                        @else
                            <div style="width:48px; height:48px; border-radius:8px; background:var(--color-bg-secondary); display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:18px; color:var(--color-text-muted);">
                                🗺️
                            </div>
                        @endif
                        <div>
                            <h4 class="nwp-heading" style="margin:0; font-size:16px;">{{ $loc->name }}</h4>
                            <div class="nwp-text-sm" style="color:#10b981; font-weight:600; text-transform:uppercase; font-size:11px; margin-top:2px;">
                                {{ is_object($loc->type) ? $loc->type->label() : $loc->type }}
                            </div>
                        </div>
                    </div>
                    <div class="nwp-text-sm nwp-text-muted" style="line-height:1.5; flex:1; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden;">
                        {{ $loc->description ?? 'No description.' }}
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>
@endsection
