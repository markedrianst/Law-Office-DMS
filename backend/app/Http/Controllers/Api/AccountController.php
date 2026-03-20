<?php
// app/Http/Controllers/Api/AccountController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    /**
     * Get authenticated user profile
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }
        
        $user->load('role');
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'role' => $user->role ? [
                    'id' => $user->role->id,
                    'name' => $user->role->name
                ] : null,
                'status' => $user->status,
                'address' => $user->address,
                'contact_number' => $user->contact_no,
                'created_at' => $user->created_at,
                'last_login' => $user->last_login
            ]
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:50',
            'middleName' => 'nullable|string|max:50',
            'lastName' => 'required|string|max:50',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'address' => 'nullable|string|max:500',
            'contact_no' => 'nullable|string|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Combine name
        $fullName = trim($request->firstName . ' ' . $request->lastName);

        $user->update([
            'full_name' => $fullName,
            'email' => $request->email,
            'address' => $request->address,
            'contact_no' => $request->contact_no ? preg_replace('/\D/', '', $request->contact_no) : null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'address' => $user->address,
                'contact_number' => $user->contact_no
            ]
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => ['required', 'string', 'confirmed', Password::min(6)],
            'new_password_confirmation' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check current password
        if (!Hash::check($request->current_password, $user->password_hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
                'errors' => [
                    'current_password' => ['The current password you entered is incorrect.']
                ]
            ], 422);
        }

        // Check if new password is same as old
        if (Hash::check($request->new_password, $user->password_hash)) {
            return response()->json([
                'success' => false,
                'message' => 'New password must be different',
                'errors' => [
                    'new_password' => ['New password must be different from current password.']
                ]
            ], 422);
        }

        // Update password
        $user->update([
            'password_hash' => Hash::make($request->new_password)
        ]);

        // Log the password change
        \App\Models\LoginLog::create([
            'user_id' => $user->id,
            'action' => 'password_change',
            'email_attempted' => $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => 'success',
            'details' => 'Password changed via account settings'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }

    /**
     * Logout from all devices
     */
    public function logoutAllDevices(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }
        
        // Revoke all tokens except current
        $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();

        // Log the action
        \App\Models\LoginLog::create([
            'user_id' => $user->id,
            'action' => 'logout_all',
            'email_attempted' => $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => 'success',
            'details' => 'Logged out from all other devices'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'All other devices have been logged out'
        ]);
    }

    /**
     * Get active sessions
     */
    public function activeSessions(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }
        
        $sessions = $user->tokens()
            ->where('expires_at', '>', now())
            ->orWhereNull('expires_at')
            ->get()
            ->map(function ($token) use ($user) {
                return [
                    'id' => $token->id,
                    'device' => $this->parseUserAgent($token->name),
                    'ip_address' => $token->ip_address ?? 'Unknown',
                    'last_used' => $token->last_used_at,
                    'created_at' => $token->created_at,
                    'is_current' => $token->id === $user->currentAccessToken()->id
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $sessions
        ]);
    }

    /**
     * Parse user agent for device info
     */
    private function parseUserAgent($userAgent)
    {
        if (str_contains($userAgent, 'Mobile')) {
            return 'Mobile Device';
        }
        if (str_contains($userAgent, 'Tablet')) {
            return 'Tablet';
        }
        return 'Desktop';
    }
}