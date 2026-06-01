<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function show(): View
    {
        return view('settings.index');
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|min:2|max:50',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($request->user()->id),
            ],
        ]);

        $request->user()->update($validated);

        return redirect()->route('settings')->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $request->user()->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('settings')->with('success', 'Password updated.');
    }

    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|file|mimes:jpeg,png,webp|max:2048',
        ]);

        $file = $request->file('avatar');
        $image = getimagesize($file->getPathname());
        if ($image[0] > 500 || $image[1] > 500) {
            return response()->json(['message' => 'Image must not exceed 500x500 pixels.'], 422);
        }

        $path = $file->store("avatars/{$request->user()->id}", 'public');
        $request->user()->update(['avatar_path' => $path]);

        return response()->json([
            'message' => 'Avatar uploaded.',
            'path' => $path,
        ]);
    }

    public function deleteAccount(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required',
        ]);

        if (!Hash::check($request->password, $request->user()->password)) {
            return back()->withErrors(['password' => 'The password is incorrect.']);
        }

        $user = $request->user();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Soft delete with 30-day grace period
        $user->delete();

        return redirect()->route('login')->with('success', 'Account scheduled for deletion. You have 30 days to reactivate.');
    }

    public function updatePreferences(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'timezone' => 'required|timezone',
            'date_format' => 'required|in:DD/MM/YYYY,MM/DD/YYYY,YYYY-MM-DD',
        ]);

        $request->user()->update($validated);

        return redirect()->route('settings')->with('success', 'Preferences updated.');
    }
}
