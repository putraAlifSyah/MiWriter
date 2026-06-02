@extends('wiki.layout')

@section('wiki_content')
@php
    // Helper to format traits
    if (!function_exists('formatWikiTrait')) {
        function formatWikiTrait($text) {
            if (empty($text)) return '<span class="nwp-text-muted" style="font-style:italic;">Not available</span>';
            if (strpos($text, "\n") !== false) {
                $lines = array_filter(array_map(function($l) {
                    return trim(preg_replace('/^[-*•]\s*/', '', $l));
                }, explode("\n", $text)));
                $uniqueLines = array_unique($lines);
                if (count($uniqueLines) > 1) {
                    return '<ul style="margin:0; padding-left:20px; display:flex; flex-direction:column; gap:8px;">' . implode('', array_map(fn($l) => "<li>" . e($l) . "</li>", $uniqueLines)) . '</ul>';
                }
                return nl2br(e(array_values($uniqueLines)[0] ?? ''));
            }
            return nl2br(e($text));
        }
    }

    // Parse notes into a timeline
    $notes = $character->notes ?? '';
    $developments = [];
    $currentChapter = null;
    $currentText = [];
    
    $lines = explode("\n", $notes);
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if (preg_match('/^\*\*(.*?):\*\*(.*)$/', $trimmed, $matches)) {
            if ($currentChapter) {
                $developments[] = [
                    'title' => $currentChapter,
                    'text' => trim(implode("\n", $currentText))
                ];
            }
            $currentChapter = trim($matches[1]);
            $currentText = [trim($matches[2])];
        } else {
            if ($trimmed !== '') {
                $currentText[] = $trimmed;
            }
        }
    }
    if ($currentChapter) {
        $developments[] = [
            'title' => $currentChapter,
            'text' => trim(implode("\n", $currentText))
        ];
    }
@endphp

<div style="margin-bottom:32px;">
    <a href="{{ route('books.wiki.characters', $book) }}" class="nwp-text-sm nwp-text-muted" style="display:inline-flex; align-items:center; gap:4px; text-decoration:none; margin-bottom:16px;">
        &larr; Back to Characters
    </a>
</div>

<!-- Header Card -->
<div class="nwp-card" style="padding:0; overflow:hidden; margin-bottom:40px; position:relative;">
    <div style="height:120px; background:linear-gradient(135deg, {{ $character->color ?? 'var(--color-accent)' }}40, var(--color-bg-secondary)); position:relative;"></div>
    <div style="padding:0 32px 32px; display:flex; flex-direction:column; align-items:center; margin-top:-60px;">
        <div style="margin-bottom:20px; position:relative; z-index:10;">
            @if($character->image_path)
                <img src="{{ asset('storage/'.$character->image_path) }}" style="width:120px; height:120px; border-radius:50%; border:4px solid var(--color-bg); box-shadow:0 8px 24px rgba(0,0,0,0.2); object-fit:cover; background:var(--color-bg-secondary);">
            @else
                <div style="width:120px; height:120px; border-radius:50%; border:4px solid var(--color-bg); box-shadow:0 8px 24px rgba(0,0,0,0.2); background:linear-gradient(135deg, var(--color-bg-secondary), var(--color-border)); display:flex; align-items:center; justify-content:center; font-size:48px; font-weight:bold; color:var(--color-text-primary);">
                    {{ strtoupper(substr($character->name, 0, 1)) }}
                </div>
            @endif
        </div>
        
        <h1 class="nwp-heading" style="margin:0 0 12px 0; font-size:36px; text-align:center;">{{ $character->name }}</h1>
        <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap; justify-content:center;">
            <span style="padding:6px 16px; border-radius:99px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); color:{{ $character->color ?? 'var(--color-accent)' }};">
                CHARACTER
            </span>
            <span style="padding:6px 16px; border-radius:99px; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; background:rgba(255,255,255,0.05); color:var(--color-text-muted);">
                {{ is_object($character->role) ? $character->role->label() : $character->role }}
            </span>
            <span style="padding:6px 16px; border-radius:99px; font-size:12px; font-weight:600; background:rgba(255,255,255,0.05); color:var(--color-text-muted);">
                Appears in <strong>{{ $chapterCount }}</strong> chapter{{ $chapterCount !== 1 ? 's' : '' }}
            </span>
        </div>
    </div>
</div>

<!-- Details Grid -->
<div style="display:grid; grid-template-columns:minmax(0, 1fr) minmax(0, 1fr); gap:24px; margin-bottom:40px;">
    <div class="nwp-card" style="padding:24px;">
        <h3 class="nwp-heading" style="font-size:16px; text-transform:uppercase; letter-spacing:0.05em; color:var(--color-text-muted); margin-bottom:16px; border-bottom:1px solid var(--color-border); padding-bottom:12px;">Physical Description</h3>
        <div style="font-size:15px; line-height:1.6; color:var(--color-text-primary);">
            {!! formatWikiTrait($character->physical_description) !!}
        </div>
    </div>
    <div class="nwp-card" style="padding:24px;">
        <h3 class="nwp-heading" style="font-size:16px; text-transform:uppercase; letter-spacing:0.05em; color:var(--color-text-muted); margin-bottom:16px; border-bottom:1px solid var(--color-border); padding-bottom:12px;">Personality Traits</h3>
        <div style="font-size:15px; line-height:1.6; color:var(--color-text-primary);">
            {!! formatWikiTrait($character->personality_traits) !!}
        </div>
    </div>
    <div class="nwp-card" style="padding:24px;">
        <h3 class="nwp-heading" style="font-size:16px; text-transform:uppercase; letter-spacing:0.05em; color:var(--color-text-muted); margin-bottom:16px; border-bottom:1px solid var(--color-border); padding-bottom:12px;">Motivations</h3>
        <div style="font-size:15px; line-height:1.6; color:var(--color-text-primary);">
            {!! formatWikiTrait($character->motivations) !!}
        </div>
    </div>
    <div class="nwp-card" style="padding:24px;">
        <h3 class="nwp-heading" style="font-size:16px; text-transform:uppercase; letter-spacing:0.05em; color:var(--color-text-muted); margin-bottom:16px; border-bottom:1px solid var(--color-border); padding-bottom:12px;">Backstory</h3>
        <div style="font-size:15px; line-height:1.6; color:var(--color-text-primary);">
            {!! formatWikiTrait($character->backstory) !!}
        </div>
    </div>
</div>

<!-- Developments Timeline -->
<h2 class="nwp-heading" style="font-size:24px; margin-bottom:24px;">📈 Development Timeline</h2>
<div class="nwp-card" style="padding:32px;">
    @if(empty($developments))
        <div class="nwp-text-muted" style="font-style:italic;">No chapter developments recorded yet. Use AI Auto-Extract in your chapters to populate this timeline.</div>
    @else
        <div style="position:relative; padding-left:24px; border-left:2px solid var(--color-border);">
            @foreach($developments as $dev)
                <div style="position:relative; margin-bottom:32px;">
                    <!-- Timeline Dot -->
                    <div style="position:absolute; left:-33px; top:4px; width:16px; height:16px; border-radius:50%; background:var(--color-bg); border:4px solid var(--color-accent);"></div>
                    
                    <h4 class="nwp-heading" style="font-size:18px; margin:0 0 8px 0; color:var(--color-accent);">{{ $dev['title'] }}</h4>
                    <div style="font-size:15px; line-height:1.6; color:var(--color-text-primary); background:rgba(255,255,255,0.02); padding:16px; border-radius:var(--radius-md); border:1px solid var(--color-border-light);">
                        {!! nl2br(e($dev['text'])) !!}
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
