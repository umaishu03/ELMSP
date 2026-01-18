<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Mail\PasswordResetMail;

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
                return redirect()->route('admin.dashboard')->with('success', 'Welcome, ' . $user->name . '!');
            } elseif ($user->isStaff()) {
                return redirect()->route('staff.dashboard')->with('success', 'Welcome, ' . $user->name . '!');
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

    /**
     * Show the forgot password form
     */
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send password reset link
     */
    public function sendPasswordResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'We could not find a user with that email address.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'We could not find a user with that email address.']);
        }

        // Generate token
        $token = Str::random(64);

        // Delete existing tokens for this email
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Insert new token
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        // Send email with token
        try {
            Mail::to($user->email)->send(new PasswordResetMail($user, $token));
            \Log::info('Password reset email sent successfully to: ' . $user->email);
        } catch (\Exception $e) {
            \Log::error('Error sending password reset email to ' . $user->email . ': ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Return with detailed error message for debugging
            return back()->withErrors([
                'email' => 'Failed to send password reset email. Error: ' . $e->getMessage() . '. Please check your email configuration or contact support.'
            ])->withInput($request->only('email'));
        }

        return back()->with('status', 'We have emailed your password reset link! Please check your inbox and spam folder.');
    }

    /**
     * Show the reset password form
     */
    public function showResetPassword(Request $request)
    {
        // Get and clean the token and email from query string
        $token = trim(urldecode($request->query('token', '')));
        $email = trim(urldecode($request->query('email', '')));
        
        // Remove any whitespace that might have been introduced by email clients
        $token = preg_replace('/\s+/', '', $token);

        if (!$token || !$email) {
            \Log::warning('Password reset attempt with missing token or email', [
                'token_present' => !empty($request->query('token')),
                'email_present' => !empty($request->query('email')),
            ]);
            return redirect()->route('password.request')->withErrors(['email' => 'Invalid reset link. Missing token or email.']);
        }

        // If token is 'success', it means password was already reset - show success message
        if ($token === 'success' && session('success')) {
            return view('auth.reset-password', [
                'token' => $token,
                'email' => $email,
            ]);
        }

        // Verify token exists
        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$passwordReset) {
            \Log::warning('Password reset attempt with non-existent email', ['email' => $email]);
            return redirect()->route('password.request')->withErrors(['email' => 'This password reset link is invalid or has expired.']);
        }

        // Check if token is valid (not expired - 60 minutes)
        $createdAt = \Carbon\Carbon::parse($passwordReset->created_at);
        if ($createdAt->copy()->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            \Log::info('Password reset token expired', ['email' => $email]);
            return redirect()->route('password.request')->withErrors(['email' => 'This password reset link has expired. Please request a new one.']);
        }

        // Verify token matches
        if (!Hash::check($token, $passwordReset->token)) {
            \Log::warning('Password reset token mismatch', [
                'email' => $email,
                'token_length' => strlen($token),
                'stored_token_length' => strlen($passwordReset->token),
            ]);
            return redirect()->route('password.request')->withErrors(['email' => 'Invalid reset link. Token does not match.']);
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    /**
     * Reset the password
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.exists' => 'We could not find a user with that email address.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
        ]);

        $token = $request->input('token');
        $email = $request->input('email');

        // Verify token
        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$passwordReset) {
            \Log::warning('Password reset attempt with non-existent token record', ['email' => $email]);
            return back()->withErrors(['email' => 'This password reset link is invalid or has expired.']);
        }

        // Check if token is valid (not expired - 60 minutes)
        $createdAt = \Carbon\Carbon::parse($passwordReset->created_at);
        if ($createdAt->copy()->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            \Log::info('Password reset token expired during reset', ['email' => $email]);
            return back()->withErrors(['email' => 'This password reset link has expired. Please request a new one.']);
        }

        // Verify token matches
        if (!Hash::check($token, $passwordReset->token)) {
            \Log::warning('Password reset token mismatch during reset', [
                'email' => $email,
                'token_length' => strlen($token),
            ]);
            return back()->withErrors(['email' => 'Invalid reset link. Token does not match.']);
        }

        // Update user password
        $user = User::where('email', $email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Delete the token
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        \Log::info('Password reset successful', ['email' => $email]);

        // Store success in session and redirect to reset password page to show success message
        // The page will then redirect to login after showing the message
        return redirect()->route('password.reset', ['token' => 'success', 'email' => $email])
            ->with('success', 'Your password has been reset successfully! Redirecting to login page...');
    }
}
