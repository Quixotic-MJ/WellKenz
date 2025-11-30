<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\UserProfile;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the user profile page.
     */
    public function index()
    {
        $user = Auth::user();
        $user->load('profile');
        
        return view('profile.index', compact('user'));
    }

    /**
     * Update user profile information.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        // Base validation rules for profile information
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'date_of_birth' => ['nullable', 'date'],
            'department' => ['nullable', 'string', 'max:100'],
            'position' => ['nullable', 'string', 'max:100'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
        ]);

        // Add password validation rules only if password fields are provided
        $passwordRules = [];
        if ($request->filled('current_password') || $request->filled('new_password') || $request->filled('new_password_confirmation')) {
            $passwordRules = [
                'current_password' => ['required', 'string'],
                'new_password' => ['required', 'string', 'min:8', 'confirmed'],
                'new_password_confirmation' => ['required', 'string'],
            ];
        }

        $validator->setRules(array_merge($validator->getRules(), $passwordRules));

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Update user basic information
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            // Handle password change if provided
            if ($request->filled('current_password')) {
                // Verify current password
                if (!Hash::check($request->current_password, $user->password_hash)) {
                    return redirect()->back()
                        ->withErrors(['current_password' => 'The current password is incorrect.'])
                        ->withInput();
                }

                // Update password
                $userData['password_hash'] = Hash::make($request->new_password);
            }

            $user->update($userData);

            // Update or create user profile
            $profileData = [
                'phone' => $request->phone,
                'address' => $request->address,
                'date_of_birth' => $request->date_of_birth,
                'department' => $request->department,
                'position' => $request->position,
                'emergency_contact_name' => $request->emergency_contact_name,
                'emergency_contact_phone' => $request->emergency_contact_phone,
            ];

            if ($user->profile) {
                $user->profile->update($profileData);
            } else {
                $user->profile()->create($profileData);
            }

            $message = 'Profile updated successfully.';
            if ($request->filled('current_password')) {
                $message = 'Profile and password updated successfully.';
            }

            return redirect()->route('profile.index')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update profile. Please try again.')
                ->withInput();
        }
    }

    /**
     * Change user password.
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
            'new_password_confirmation' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password_hash)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'The current password is incorrect.'])
                ->withInput();
        }

        try {
            // Update password_hash directly since that's the actual database column
            $user->update([
                'password_hash' => Hash::make($request->new_password),
            ]);

            return redirect()->route('profile.index')->with('success', 'Password changed successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to change password. Please try again.')
                ->withInput();
        }
    }

    /**
     * Update profile photo (placeholder for future implementation).
     */
    public function updatePhoto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // This is a placeholder for future photo upload implementation
        return redirect()->route('profile.index')->with('info', 'Profile photo upload feature coming soon.');
    }
}