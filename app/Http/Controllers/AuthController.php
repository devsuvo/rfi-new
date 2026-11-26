<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $cred = $request->validate(['email' => 'required|email', 'password' => 'required']);
        if (! Auth::attempt($cred, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email or password is incorrect.'])->onlyInput('email');
        }
        $user = Auth::user();
        if ($user->accessExpired()) {
            Auth::logout();

            return back()->withErrors(['email' => 'Your access ended on '.$user->access_until->format('d M Y').'. Please contact the office.'])->onlyInput('email');
        }
        $request->session()->regenerate();

        // Admin / teacher: confirm Reading or Writing first.
        return $user->isStaff() ? redirect()->route('admin.choose') : redirect()->intended(route('home'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
