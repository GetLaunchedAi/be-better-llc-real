<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Show admin login form.
     */
    public function showLogin()
    {
        if (Auth::check() && Auth::user()->hasAdminAccess()) {
            return redirect()->route('admin.products.index');
        }

        return view('admin.auth.login');
    }

    /**
     * Handle login attempt.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            Log::channel('security')->warning('Failed login attempt', [
                'email' => $request->input('email'),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Invalid credentials.']);
        }

        $user = Auth::user();

        if (! $user->hasAdminAccess()) {
            Auth::logout();
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Your account does not have admin access.']);
        }

        // Update last login
        $user->update(['last_login_at' => now()]);

        Log::channel('security')->info('Successful login', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
        ]);

        $request->session()->regenerate();

        ActivityLog::log('login', $user, null, $user->name);

        return redirect()->intended(route('admin.products.index'));
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        ActivityLog::log('logout', Auth::user(), null, Auth::user()->name);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')
            ->with('success', 'Signed out successfully.');
    }
}

