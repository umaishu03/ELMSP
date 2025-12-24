<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'role' => 'required|in:admin,staff',
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Check if the user's role matches the selected role
            if ($request->role === 'admin' && !$user->isAdmin()) {
                Auth::logout();
                return back()->withErrors([
                    'role' => 'You are not authorized to login as Admin.',
                ])->withInput($request->only('email', 'role'));
            }
            
            if ($request->role === 'staff' && !$user->isStaff()) {
                Auth::logout();
                return back()->withErrors([
                    'role' => 'You are not authorized to login as Staff.',
                ])->withInput($request->only('email', 'role'));
            }
            
            // Redirect based on role
            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard')->with('success', 'Welcome, Administrator!');
            } elseif ($user->isStaff()) {
                return redirect()->route('staff.dashboard')->with('success', 'Welcome, Staff Member!');
            }
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email', 'role'));
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }

    /**
     * Show admin dashboard
     */
    public function adminDashboard()
    {
        $user = Auth::user();
        return view('dashboard.admin', compact('user'));
    }

    /**
     * Show staff dashboard
     */
    public function staffDashboard()
    {
        $user = Auth::user();
        return view('dashboard.staff', compact('user'));
    }
}
