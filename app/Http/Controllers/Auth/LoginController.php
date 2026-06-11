<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'Username_User' => 'required',
            'Password_User' => 'required'
        ]);

        $user = \App\Models\User::where('Username_User', $credentials['Username_User'])
                                ->where('Password_User', $credentials['Password_User'])
                                ->first();

        if ($user) {
            Auth::login($user);
            $request->session()->regenerate();

            // Redirect based on user type
            $request->session()->forget('url.intended');

            if ($user->isAdmin()) {
                return redirect()->to(route('admin.dashboard'));
            } elseif ($user->isLeader()) {
                return redirect()->to(route('leader.dashboard'));
            } elseif ($user->isArea()) {
                return redirect()->to(route('area.dashboard'));
            }

            return redirect()->to(route('login'));
        }

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
