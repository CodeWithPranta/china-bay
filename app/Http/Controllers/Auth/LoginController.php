<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function show(): View
    {
        return view('pages.auth.login');
    }

    /**
     * Authenticate with email or phone + password.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:190'],
            'password' => ['required', 'string'],
        ]);

        $field = filter_var($data['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        if (! Auth::attempt([$field => $data['login'], 'password' => $data['password']], $request->boolean('remember'))) {
            return back()->withErrors(['login' => __('auth.failed')])->onlyInput('login');
        }

        $request->session()->regenerate();

        $user = $request->user();

        if ($user?->hasAnyRole(['super-admin', 'admin', 'manager', 'support'])) {
            return redirect()->intended('/admin');
        }

        return redirect()->intended('/');
    }

    /**
     * Log out.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
