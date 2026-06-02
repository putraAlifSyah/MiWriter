@extends('wiki.layout')

@section('wiki_content')
<div style="margin-bottom:40px;">
    <h2 class="nwp-heading" style="font-size:32px; margin-bottom:8px;">{{ $book->title }} Overview</h2>
    <p class="nwp-text-muted">A high-level view of your story and recent character developments.</p>
</div>

<div style="display:flex; flex-direction:column; gap:40px;">
    <!-- Story Premise -->
    <section>
        <h3 class="nwp-heading" style="font-size:24px; border-bottom:2px solid var(--color-border); padding-bottom:8px; margin-bottom:20px;">📖 Story Premise</h3>
        <div class="nwp-card" style="padding:24px; line-height:1.6; font-size:15px;">
            @if($book->premise)
                {!! nl2br(e($book->premise)) !!}
            @else
                <span class="nwp-text-muted" style="font-style:italic;">No premise written yet. Head to your book settings or use the Plot Architect to generate one.</span>
            @endif
        </div>
    </section>

    <!-- Most Active Characters -->
    <section>
        <h3 class="nwp-heading" style="font-size:24px; border-bottom:2px solid var(--color-border); padding-bottom:8px; margin-bottom:20px;">📈 Most Active Characters</h3>
        @if($activeCharacters->isEmpty())
            <div class="nwp-text-muted" style="font-style:italic;">No character developments recorded yet. AI Auto-Extract will populate this as you write.</div>
        @else
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:20px;">
                @foreach($activeCharacters as $char)
                    @php
                        $chapterCount = substr_count($char->notes ?? '', ':**');
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
                        <div class="nwp-text-sm nwp-text-muted" style="line-height:1.5; flex:1;">
                            Appears in <strong style="color:var(--color-text-primary);">{{ $chapterCount }}</strong> chapter{{ $chapterCount !== 1 ? 's' : '' }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection
