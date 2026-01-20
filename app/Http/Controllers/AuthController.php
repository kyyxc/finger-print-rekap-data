<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login()
    {
        $role = auth()->guard('role')?->user()?->role;

        if($role == "admin"){
            return redirect()->route('admin.dashboard');
        }

        if($role == "sekretaris"){
            return redirect()->route('sekretaris.dashboard');
        }

        return view('pages.auth.login');
    }

    public function postLogin(Request $request)
    {
        $credentials = $request->only('username', 'password');
        $remember = $request->has('remember');

        if (auth()->guard('role')->attempt($credentials, $remember)) {
            $user = auth()->guard('role')->user();
            $role = $user->role;

            if ($role == "admin") {
                return redirect()->route('admin.dashboard')->with('message', 'Login berhasil');
            }

            return redirect()->route('sekretaris.dashboard')->with('message', 'Login berhasil');
        } else {
            auth()->guard('admin')->logout();
        }

        return redirect()->back()->withErrors(['login' => 'Invalid credentials']);
    }

    public function logout(Request $request)
    {
        auth()->guard('role')->logout();
        return redirect()->route('login')->with('message', 'Logged out successfully');
    }
}
