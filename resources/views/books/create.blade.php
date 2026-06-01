@extends('layouts.app')

@section('content')
<h1 class="nwp-heading nwp-mb-3">Create New Book</h1>

<div class="nwp-card" style="max-width:500px;">
    <form method="POST" action="{{ route('books.store') }}">
        @csrf

        <div class="nwp-form-group">
            <label class="nwp-label" for="title">Book Title</label>
            <input type="text" id="title" name="title" value="{{ old('title') }}"
                   class="nwp-input @error('title') nwp-input--error @enderror"
                   required autofocus maxlength="200">
            @error('title')
                <p class="nwp-error">{{ $message }}</p>
            @enderror
        </div>

        <div style="display:flex; gap:12px;">
            <button type="submit" class="nwp-btn">Create Book</button>
            <a href="{{ route('books.index') }}" class="nwp-btn nwp-btn--secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
