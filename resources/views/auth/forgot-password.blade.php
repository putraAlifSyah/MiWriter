@extends('layouts.guest')

@section('subtitle', 'Reset your password')

@section('content')
@if(session('success'))
    <div class="nwp-toast nwp-toast--success" style="position:relative; top:0; right:0; margin-bottom:16px;">
        {{ session('success') }}
    </div>
@endif

<form method="POST" action="{{ route('password.email') }}">
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

    <button type="submit" class="nwp-btn nwp-btn--block">Send Reset Link</button>

    <p class="nwp-text-center nwp-mt-2 nwp-text-sm">
        <a href="{{ route('login') }}">Back to login</a>
    </p>
</form>
@endsection
