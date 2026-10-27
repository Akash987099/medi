<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginController extends Controller
{
    public function adminLogin()
    {
        return view('login');
    }

    public function adminLogins(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'redirect' => route('admin.index'),
                ]);
            }

            return redirect()->route('admin.index');
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid email or password.',
            ], 422);
        }

        return back()
            ->withErrors(['email' => 'Invalid email or password.'])
            ->onlyInput('email');
    }

    public function userLogin()
    {
        return view('users.login');
    }

    public function userLogins(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:8'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('username', $credentials['username'])
            ->whereIn('role', [1, 2])
            ->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            Auth::guard('user')->login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'redirect' => route('user.index'),
                ]);
            }

            return redirect()->route('user.index');
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid username or password.',
            ], 422);
        }

        return back()
            ->withErrors(['username' => 'Invalid username or password.'])
            ->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function userLogout(Request $request)
    {
        Auth::guard('user')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
