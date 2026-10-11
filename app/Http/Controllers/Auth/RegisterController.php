<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * Show the customer registration form.
     */
    public function show(): View
    {
        return view('pages.auth.register');
    }

    /**
     * Create the customer, assign the customer role, log them in.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'regex:/^01[0-9]{9}$/', 'unique:users,phone'],
            'email' => ['nullable', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'password' => $data['password'],
            'locale' => app()->getLocale(),
            'referral_code' => self::uniqueReferralCode(),
            'loyalty_points' => 20,
        ]);

        $user->assignRole('customer');

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended('/account')->with('status', 'Welcome to ChinaBay! Your account is ready. · স্বাগতম!');
    }

    protected static function uniqueReferralCode(): string
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (User::where('referral_code', $code)->exists());

        return $code;
    }
}
