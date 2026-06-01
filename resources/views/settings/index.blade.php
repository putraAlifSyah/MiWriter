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

<!-- AI Settings -->
<div class="nwp-card nwp-mb-3">
    <h2 class="nwp-heading nwp-mb-2" style="font-size:var(--font-size-lg);">AI Assistant</h2>
    <p class="nwp-text-sm nwp-text-muted nwp-mb-2">Connect your own AI provider to get writing assistance. Your API key is stored securely and never shared.</p>
    <form id="ai-settings-form">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="nwp-form-group">
                <label class="nwp-label" for="ai_provider">Provider</label>
                <select id="ai_provider" name="ai_provider" class="nwp-select" required>
                    <option value="">Select provider...</option>
                    <option value="openai" {{ auth()->user()->ai_provider === 'openai' ? 'selected' : '' }}>OpenAI</option>
                    <option value="anthropic" {{ auth()->user()->ai_provider === 'anthropic' ? 'selected' : '' }}>Anthropic (Claude)</option>
                    <option value="google" {{ auth()->user()->ai_provider === 'google' ? 'selected' : '' }}>Google (Gemini)</option>
                    <option value="groq" {{ auth()->user()->ai_provider === 'groq' ? 'selected' : '' }}>Groq</option>
                    <option value="openrouter" {{ auth()->user()->ai_provider === 'openrouter' ? 'selected' : '' }}>OpenRouter</option>
                </select>
            </div>
            <div class="nwp-form-group">
                <label class="nwp-label" for="ai_model">Model</label>
                <input type="text" id="ai_model" name="ai_model" value="{{ auth()->user()->ai_model }}" class="nwp-input" placeholder="e.g. gpt-4o, claude-3-haiku-20240307" required>
            </div>
        </div>
        <div class="nwp-form-group">
            <label class="nwp-label" for="ai_api_key">API Key</label>
            <input type="password" id="ai_api_key" name="ai_api_key" value="{{ auth()->user()->ai_api_key }}" class="nwp-input" placeholder="sk-..." required>
        </div>
        <div style="display:flex; align-items:center; gap:12px;">
            <button type="submit" class="nwp-btn nwp-btn--sm">Save AI Settings</button>
            <span id="ai-settings-status" class="nwp-text-sm"></span>
        </div>
    </form>
</div>

<script>
document.getElementById('ai-settings-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(this));
    const status = document.getElementById('ai-settings-status');
    status.textContent = 'Saving...';
    status.style.color = 'var(--color-text-muted)';

    fetch('{{ route("ai.settings") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify(data),
    })
    .then(r => { if (!r.ok) throw r; return r.json(); })
    .then(d => {
        status.textContent = 'Saved!';
        status.style.color = 'var(--color-success)';
    })
    .catch(err => {
        status.textContent = 'Failed to save.';
        status.style.color = 'var(--color-danger)';
    });
});
</script>

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
