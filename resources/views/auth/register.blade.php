@extends('layouts.guest')

@section('subtitle', 'Create your account')

@section('content')
<form method="POST" action="{{ route('register') }}">
    @csrf

    <div class="nwp-form-group">
        <label class="nwp-label" for="name">Name</label>
        <input type="text" id="name" name="name" value="{{ old('name') }}"
               class="nwp-input @error('name') nwp-input--error @enderror"
               required autofocus>
        @error('name')
            <p class="nwp-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="nwp-form-group">
        <label class="nwp-label" for="email">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}"
               class="nwp-input @error('email') nwp-input--error @enderror"
               required>
        @error('email')
            <p class="nwp-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="nwp-form-group">
        <label class="nwp-label" for="password">Password</label>
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

    <button type="submit" class="nwp-btn nwp-btn--block">Register</button>

    <p class="nwp-text-center nwp-mt-2 nwp-text-sm">
        Already have an account? <a href="{{ route('login') }}">Log in</a>
    </p>
</form>
@endsection
