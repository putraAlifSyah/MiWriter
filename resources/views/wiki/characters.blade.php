@extends('wiki.layout')

@section('wiki_content')
<div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:32px;">
    <div>
        <h2 class="nwp-heading" style="font-size:32px; margin-bottom:8px;">Characters</h2>
        <p class="nwp-text-muted">The people and creatures of your story.</p>
    </div>
    
    <form method="GET" action="{{ route('books.wiki.characters', $book) }}" style="display:flex; align-items:center; gap:8px;">
        <label for="role" class="nwp-text-sm nwp-text-muted">Filter Role:</label>
        <select name="role" id="role" class="nwp-input nwp-input--sm" style="width:120px;" onchange="this.form.submit()">
            <option value="all" {{ request('role') == 'all' || !request('role') ? 'selected' : '' }}>All Roles</option>
            <option value="protagonist" {{ request('role') == 'protagonist' ? 'selected' : '' }}>Protagonist</option>
            <option value="supporting" {{ request('role') == 'supporting' ? 'selected' : '' }}>Supporting</option>
            <option value="minor" {{ request('role') == 'minor' ? 'selected' : '' }}>Minor</option>
        </select>
    </form>
</div>

<section id="characters">
    @if($characters->isEmpty())
        <div class="nwp-text-muted" style="font-style:italic;">No characters found.</div>
    @else
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:20px;">
            @foreach($characters as $char)
                @php
                    $charDetails = [
                        'Image' => $char->image_path ? asset('storage/'.$char->image_path) : null,
                        'Physical Description' => $char->physical_description,
                        'Personality Traits' => $char->personality_traits,
                        'Notes' => $char->notes,
                    ];
                    $jsonData = json_encode([
                        'type' => 'Character',
                        'name' => $char->name,
                        'color' => $char->color,
                        'desc' => $char->role ? (is_object($char->role) ? $char->role->label() : $char->role) : 'Unknown',
                        'details' => $charDetails
                    ]);
                @endphp
                    <div class="nwp-card" style="display:flex; flex-direction:column; padding:16px; cursor:pointer; transition:transform 0.2s, box-shadow 0.2s;" onclick="window.location.href='{{ route('books.wiki.characters.show', [$book, $char]) }}'" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px;">
                        @if($char->image_path)
                            <img src="{{ asset('storage/'.$char->image_path) }}" style="width:48px; height:48px; border-radius:50%; object-fit:cover;">
                        @else
                            <div style="width:48px; height:48px; border-radius:50%; background:var(--color-bg-secondary); display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:18px; color:var(--color-text-muted);">
                                {{ strtoupper(substr($char->name, 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            <h4 class="nwp-heading" style="margin:0; font-size:16px;">{{ $char->name }}</h4>
                            <div class="nwp-text-sm" style="color:var(--color-accent); font-weight:600; text-transform:uppercase; font-size:11px; margin-top:2px;">
                                {{ is_object($char->role) ? $char->role->label() : $char->role }}
                            </div>
                        </div>
                    </div>
                    <div class="nwp-text-sm nwp-text-muted" style="line-height:1.5; flex:1; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden;">
                        {{ $char->physical_description ?? 'No description.' }}
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>
@endsection
