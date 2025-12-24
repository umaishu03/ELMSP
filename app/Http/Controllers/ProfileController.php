<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Show the profile page
     */
    public function show()
    {
        $user = Auth::user();
        return view('auth.profile', compact('user'));
    }

    /**
     * Update the user's profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // Validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:1000',
        ];

        // Add password validation if password fields are provided
        if ($request->filled('current_password') || $request->filled('password') || $request->filled('password_confirmation')) {
            $rules['current_password'] = 'required|string';
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $request->validate($rules);

        // Verify current password if password change is requested
        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return redirect()->route('profile.show')
                    ->withErrors(['current_password' => 'The current password is incorrect.'])
                    ->withInput();
            }
        }

        // Prepare update data
        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ];

        // Add password to update data if provided
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
            $updateData['first_login'] = false; // Mark that user has changed password
        }

        $user->update($updateData);

        $message = 'Profile updated successfully!';
        if ($request->filled('password')) {
            $message .= ' Your password has been changed.';
        }

        return redirect()->route('profile.show')->with('success', $message);
    }
}