<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        $timeout = config('session.lifetime') * 60; // Convert minutes to seconds

        if (Auth::check()) {
            $lastActivity = session('last_activity_time');

            if ($lastActivity && (time() - $lastActivity) > $timeout) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('error', 'Your session has expired due to inactivity.');
            }

            session(['last_activity_time' => time()]);
        }

        return $next($request);
    }
}
