<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRegistrationRequest;
use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(StoreRegistrationRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $email = $request->email;

        // Check for lockout
        $attempt = LoginAttempt::where('email', $email)->first();

        if ($attempt && $attempt->isLocked()) {
            $minutes = $attempt->locked_until->diffInMinutes(now());
            return back()->withErrors([
                'email' => "Account is temporarily locked. Please try again in {$minutes} minutes.",
            ])->withInput($request->only('email'));
        }

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();
            session(['last_activity_time' => time()]);

            // Reset login attempts on success
            if ($attempt) {
                $attempt->update(['attempts' => 0, 'locked_until' => null]);
            }

            return redirect()->intended(route('dashboard'));
        }

        // Track failed attempt
        $attempt = LoginAttempt::firstOrCreate(
            ['email' => $email],
            ['attempts' => 0]
        );

        $attempt->increment('attempts');

        if ($attempt->attempts >= 5) {
            $attempt->update(['locked_until' => now()->addMinutes(15)]);
            return back()->withErrors([
                'email' => 'Account is temporarily locked due to too many failed attempts. Please try again in 15 minutes.',
            ])->withInput($request->only('email'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    public function forgotPassword(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email']);

        // Rate limiting check
        $email = $request->email;
        $recentAttempts = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('created_at', '>=', now()->subMinutes(15))
            ->count();

        if ($recentAttempts >= 5) {
            return back()->withErrors([
                'email' => 'Too many password reset requests. Please wait before trying again.',
            ]);
        }

        Password::sendResetLink($request->only('email'));

        // Always show same message regardless of email existence
        return back()->with('success', 'If the email is associated with an account, a reset link has been sent.');
    }

    public function showResetPassword(string $token): View
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                // Terminate all sessions
                DB::table('sessions')
                    ->where('user_id', $user->id)
                    ->delete();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', 'Your password has been reset. Please log in.');
        }

        return back()->withErrors(['email' => __($status)]);
    }
}
