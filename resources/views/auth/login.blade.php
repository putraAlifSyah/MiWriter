@extends('layouts.guest')

@section('subtitle', 'Sign in to your account')

@section('content')
@if(request('expired'))
    <div class="nwp-toast nwp-toast--error" style="position:relative; top:0; right:0; margin-bottom:16px;">
        Your session has expired. Please log in again.
    </div>
@endif

@if(session('success'))
    <div class="nwp-toast nwp-toast--success" style="position:relative; top:0; right:0; margin-bottom:16px;">
        {{ session('success') }}
    </div>
@endif

<form method="POST" action="{{ route('login') }}">
    @csrf

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
        <label class="nwp-label" for="password">Password</label>
        <input type="password" id="password" name="password"
               class="nwp-input @error('password') nwp-input--error @enderror"
               required>
    </div>

    <div class="nwp-form-group" style="display:flex; justify-content:space-between; align-items:center;">
        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
            <input type="checkbox" name="remember"> <span class="nwp-text-sm">Remember me</span>
        </label>
        <a href="{{ route('password.request') }}" class="nwp-text-sm">Forgot password?</a>
    </div>

    <button type="submit" class="nwp-btn nwp-btn--block">Log In</button>

    <p class="nwp-text-center nwp-mt-2 nwp-text-sm">
        Don't have an account? <a href="{{ route('register') }}">Register</a>
    </p>
</form>
@endsection
