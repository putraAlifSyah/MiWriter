@extends('layouts.app')

@section('content')
<h1 class="nwp-heading nwp-mb-3">Settings</h1>

<!-- Profile -->
<div class="nwp-card nwp-mb-3">
    <h2 class="nwp-heading nwp-mb-2" style="font-size:var(--font-size-lg);">Profile</h2>
    <form method="POST" action="{{ route('settings.profile') }}">
        @csrf @method('PUT')
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="nwp-form-group">
                <label class="nwp-label" for="name">Name</label>
                <input type="text" id="name" name="name" value="{{ auth()->user()->name }}"
                       class="nwp-input @error('name') nwp-input--error @enderror" required>
                @error('name') <p class="nwp-error">{{ $message }}</p> @enderror
            </div>
            <div class="nwp-form-group">
                <label class="nwp-label" for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ auth()->user()->email }}"
                       class="nwp-input @error('email') nwp-input--error @enderror" required>
                @error('email') <p class="nwp-error">{{ $message }}</p> @enderror
            </div>
        </div>
        <button type="submit" class="nwp-btn nwp-btn--sm">Update Profile</button>
    </form>
</div>

<!-- Password -->
<div class="nwp-card nwp-mb-3">
    <h2 class="nwp-heading nwp-mb-2" style="font-size:var(--font-size-lg);">Change Password</h2>
    <form method="POST" action="{{ route('settings.password') }}">
        @csrf @method('PUT')
        <div class="nwp-form-group">
            <label class="nwp-label" for="current_password">Current Password</label>
            <input type="password" id="current_password" name="current_password" class="nwp-input @error('current_password') nwp-input--error @enderror" required>
            @error('current_password') <p class="nwp-error">{{ $message }}</p> @enderror
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="nwp-form-group">
                <label class="nwp-label" for="password">New Password</label>
                <input type="password" id="password" name="password" class="nwp-input" required>
            </div>
            <div class="nwp-form-group">
                <label class="nwp-label" for="password_confirmation">Confirm</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="nwp-input" required>
            </div>
        </div>
        <button type="submit" class="nwp-btn nwp-btn--sm">Update Password</button>
    </form>
</div>

<!-- Preferences -->
<div class="nwp-card nwp-mb-3">
    <h2 class="nwp-heading nwp-mb-2" style="font-size:var(--font-size-lg);">Preferences</h2>
    <form method="POST" action="{{ route('settings.preferences') }}">
        @csrf @method('PUT')
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="nwp-form-group">
                <label class="nwp-label" for="timezone">Timezone</label>
                <select id="timezone" name="timezone" class="nwp-select">
                    @foreach(timezone_identifiers_list() as $tz)
                        <option value="{{ $tz }}" {{ auth()->user()->timezone === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                    @endforeach
                </select>
            </div>
            <div class="nwp-form-group">
                <label class="nwp-label">Date Format</label>
                @foreach(['DD/MM/YYYY', 'MM/DD/YYYY', 'YYYY-MM-DD'] as $format)
                    <label style="display:block; margin-top:8px; cursor:pointer;">
                        <input type="radio" name="date_format" value="{{ $format }}" {{ auth()->user()->date_format === $format ? 'checked' : '' }}>
                        {{ $format }}
                    </label>
                @endforeach
            </div>
        </div>
        <button type="submit" class="nwp-btn nwp-btn--sm">Save Preferences</button>
    </form>
</div>

<!-- Danger Zone -->
<div class="nwp-card" style="border-color:var(--color-danger);">
    <h2 class="nwp-heading nwp-mb-2" style="font-size:var(--font-size-lg); color:var(--color-danger);">Danger Zone</h2>
    <p class="nwp-text-sm nwp-mb-2">Deleting your account will permanently remove all data after a 30-day grace period.</p>
    <form method="POST" action="{{ route('settings.account') }}" onsubmit="return confirm('Are you sure? This action cannot be undone after 30 days.')">
        @csrf @method('DELETE')
        <div class="nwp-form-group">
            <label class="nwp-label" for="delete_password">Confirm Password</label>
            <input type="password" id="delete_password" name="password" class="nwp-input" required>
        </div>
        <button type="submit" class="nwp-btn nwp-btn--danger nwp-btn--sm">Delete Account</button>
    </form>
</div>
@endsection
