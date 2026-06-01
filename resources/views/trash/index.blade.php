@extends('layouts.app')

@section('content')
<div class="nwp-container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <h1 class="nwp-heading">Recycle Bin</h1>
    </div>

    @if($books->isEmpty() && $chapters->isEmpty() && $characters->isEmpty())
        <div class="nwp-card" style="text-align:center; padding:48px 24px;">
            <div style="font-size:48px; margin-bottom:16px;">🗑️</div>
            <h3 class="nwp-heading" style="margin-bottom:8px;">Trash is empty</h3>
            <p class="nwp-text-muted">You have no deleted items.</p>
        </div>
    @else
        <div class="nwp-dashboard-grid" style="grid-template-columns: 1fr;">
            
            @if($books->isNotEmpty())
            <div class="nwp-card">
                <h3 class="nwp-heading" style="font-size:var(--font-size-lg); margin-bottom:16px;">Deleted Books</h3>
                <table style="width:100%; text-align:left; border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:1px solid var(--color-border); color:var(--color-text-muted); font-size:var(--font-size-sm);">
                            <th style="padding:8px 0;">Title</th>
                            <th style="padding:8px 0;">Deleted At</th>
                            <th style="padding:8px 0; text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($books as $book)
                        <tr style="border-bottom:1px solid var(--color-border-light);">
                            <td style="padding:12px 0;"><strong>{{ $book->title }}</strong></td>
                            <td style="padding:12px 0; color:var(--color-text-muted); font-size:var(--font-size-sm);">{{ $book->deleted_at->diffForHumans() }}</td>
                            <td style="padding:12px 0; text-align:right; display:flex; gap:8px; justify-content:flex-end;">
                                <form method="POST" action="{{ route('trash.restore', ['type' => 'book', 'id' => $book->id]) }}">
                                    @csrf
                                    <button class="nwp-btn nwp-btn--sm nwp-btn--primary">Restore</button>
                                </form>
                                <form method="POST" action="{{ route('trash.forceDelete', ['type' => 'book', 'id' => $book->id]) }}" onsubmit="return confirm('Permanently delete this book and all its contents? This cannot be undone.')">
                                    @csrf @method('DELETE')
                                    <button class="nwp-btn nwp-btn--sm nwp-btn--danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            @if($chapters->isNotEmpty())
            <div class="nwp-card">
                <h3 class="nwp-heading" style="font-size:var(--font-size-lg); margin-bottom:16px;">Deleted Chapters</h3>
                <table style="width:100%; text-align:left; border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:1px solid var(--color-border); color:var(--color-text-muted); font-size:var(--font-size-sm);">
                            <th style="padding:8px 0;">Title</th>
                            <th style="padding:8px 0;">Book</th>
                            <th style="padding:8px 0;">Deleted At</th>
                            <th style="padding:8px 0; text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($chapters as $chapter)
                        <tr style="border-bottom:1px solid var(--color-border-light);">
                            <td style="padding:12px 0;"><strong>{{ $chapter->title }}</strong></td>
                            <td style="padding:12px 0; font-size:var(--font-size-sm);">{{ $chapter->book ? $chapter->book->title : 'Unknown' }}</td>
                            <td style="padding:12px 0; color:var(--color-text-muted); font-size:var(--font-size-sm);">{{ $chapter->deleted_at->diffForHumans() }}</td>
                            <td style="padding:12px 0; text-align:right; display:flex; gap:8px; justify-content:flex-end;">
                                <form method="POST" action="{{ route('trash.restore', ['type' => 'chapter', 'id' => $chapter->id]) }}">
                                    @csrf
                                    <button class="nwp-btn nwp-btn--sm nwp-btn--primary">Restore</button>
                                </form>
                                <form method="POST" action="{{ route('trash.forceDelete', ['type' => 'chapter', 'id' => $chapter->id]) }}" onsubmit="return confirm('Permanently delete this chapter?')">
                                    @csrf @method('DELETE')
                                    <button class="nwp-btn nwp-btn--sm nwp-btn--danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            @if($characters->isNotEmpty())
            <div class="nwp-card">
                <h3 class="nwp-heading" style="font-size:var(--font-size-lg); margin-bottom:16px;">Deleted Characters</h3>
                <table style="width:100%; text-align:left; border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:1px solid var(--color-border); color:var(--color-text-muted); font-size:var(--font-size-sm);">
                            <th style="padding:8px 0;">Name</th>
                            <th style="padding:8px 0;">Book</th>
                            <th style="padding:8px 0;">Deleted At</th>
                            <th style="padding:8px 0; text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($characters as $character)
                        <tr style="border-bottom:1px solid var(--color-border-light);">
                            <td style="padding:12px 0;"><strong>{{ $character->name }}</strong></td>
                            <td style="padding:12px 0; font-size:var(--font-size-sm);">{{ $character->book ? $character->book->title : 'Unknown' }}</td>
                            <td style="padding:12px 0; color:var(--color-text-muted); font-size:var(--font-size-sm);">{{ $character->deleted_at->diffForHumans() }}</td>
                            <td style="padding:12px 0; text-align:right; display:flex; gap:8px; justify-content:flex-end;">
                                <form method="POST" action="{{ route('trash.restore', ['type' => 'character', 'id' => $character->id]) }}">
                                    @csrf
                                    <button class="nwp-btn nwp-btn--sm nwp-btn--primary">Restore</button>
                                </form>
                                <form method="POST" action="{{ route('trash.forceDelete', ['type' => 'character', 'id' => $character->id]) }}" onsubmit="return confirm('Permanently delete this character?')">
                                    @csrf @method('DELETE')
                                    <button class="nwp-btn nwp-btn--sm nwp-btn--danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

        </div>
    @endif
</div>
@endsection
