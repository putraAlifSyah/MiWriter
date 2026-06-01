@extends('layouts.guest')

@section('subtitle', 'Set a new password')

@section('content')
<form method="POST" action="{{ route('password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <div class="nwp-form-group">
        <label class="nwp-label" for="email">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}"
               class="nwp-input @error('email') nwp-input--error @enderror"
               required autofocus>
        @error('email')
            <p class="nwp-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="nwp-form-group">
        <label class="nwp-label" for="password">New Password</label>
        <input type="password" id="password" name="password"
               class="nwp-input @error('password') nwp-input--error @enderror"
               required>
        @error('password')
            <p class="nwp-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="nwp-form-group">
        <label class="nwp-label" for="password_confirmation">Confirm Password</label>
        <input type="password" id="password_confirmation" name="password_confirmation"
               class="nwp-input" required>
    </div>

    <button type="submit" class="nwp-btn nwp-btn--block">Reset Password</button>
</form>
@endsection
