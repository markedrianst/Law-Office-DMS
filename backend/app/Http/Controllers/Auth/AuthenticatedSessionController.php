<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\LoginLog;

class AuthenticatedSessionController extends Controller
{
    // ─── LOGIN ───────────────────────────────────────────────────────────────

    public function login(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get user with role
        $user = User::with('role:id,name')->where('email', $request->email)->first();

        // Check if user exists
        if (!$user) {
            $this->writeLoginLog($request, null, $request->email, 'failed', 'Email not found');
            return response()->json([
                'message' => 'Invalid credentials',
                'errors' => [
                    'email' => ['The provided credentials are incorrect.']
                ]
            ], 401);
        }

        // Check password
        if (!Hash::check($request->password, $user->password_hash)) {
            $this->writeLoginLog($request, $user->id, $request->email, 'failed', 'Incorrect password');
            return response()->json([
                'message' => 'Invalid credentials',
                'errors' => [
                    'password' => ['The provided credentials are incorrect.']
                ]
            ], 401);
        }

        // Check if account is active
        if ($user->status !== 'active') {
            $this->writeLoginLog($request, $user->id, $request->email, 'failed', 'Account inactive');
            return response()->json([
                'message' => 'Account inactive',
                'errors' => [
                    'email' => ['Your account is inactive. Please contact the administrator.']
                ]
            ], 403);
        }

        // Check if password change is required
        if ($user->must_change_password) {
            return response()->json([
                'message' => 'Password change required',
                'requires_password_change' => true,
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'full_name' => $user->full_name,
                    'must_change_password' => true
                ]
            ], 200);
        }

        // ✅ FIXED: Only delete expired tokens, not all tokens
        // This way user can be logged in on multiple devices
        $user->tokens()->where('expires_at', '<', now())->delete();

        // Create new token
        $token = $user->createToken('auth_token', ['*'], now()->addDays(7))->plainTextToken;

        // Update last login
        $user->last_login = now();
        $user->save();

        // Write success log
        $this->writeLoginLog($request, $user->id, $user->email, 'success', 'Login successful');

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'full_name' => $user->full_name,
                'role' => $user->role ? ['id' => $user->role->id, 'name' => $user->role->name] : null,
                'status' => $user->status,
                'last_login' => $user->last_login
            ]
        ]);
    }

    // ─── CHANGE PASSWORD ─────────────────────────────────────────────────────

    public function change(Request $request)
    {
        // Validate all inputs
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
            'new_password_confirmation' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find user
        $user = User::where('email', $request->email)->first();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password_hash)) {
            return response()->json([
                'message' => 'Current password is incorrect',
                'errors' => [
                    'current_password' => ['The current password you entered is incorrect.']
                ]
            ], 422);
        }

        // Check if new password is same as old
        if (Hash::check($request->new_password, $user->password_hash)) {
            return response()->json([
                'message' => 'New password must be different',
                'errors' => [
                    'new_password' => ['New password must be different from current password.']
                ]
            ], 422);
        }

        // Update password
        $user->password_hash = Hash::make($request->new_password);
        $user->must_change_password = false;
        $user->save();

        // Log the password change
        $this->writeLoginLog($request, $user->id, $user->email, 'success', 'Password changed successfully');

        // ✅ FIXED: Don't delete tokens on password change
        // Just return success, user can stay logged in

        return response()->json([
            'message' => 'Password updated successfully.'
        ]);
    }

    // ─── LOGOUT ─────────────────────────────────────────────────────────────

   /**
 * Logout user - FIXED
 */
public function logout(Request $request)
{
    try {
        $user = $request->user();

        if ($user) {
            // Log the logout
            $this->writeLoginLog($request, $user->id, $user->email, 'success', 'Logout successful');
            
            // ✅ FIXED: Check if there's a current token before deleting
            if ($request->user() && method_exists($request->user(), 'currentAccessToken') && $request->user()->currentAccessToken()) {
                $request->user()->currentAccessToken()->delete();
            } else {
                // Fallback: delete all tokens for this user
                $user->tokens()->delete();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);

    } catch (\Exception $e) {
        \Log::error('Logout error: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Logout failed'
        ], 500);
    }
}

    // ─── GET USER DATA (for session restore) ────────────────────────────────

    public function getUserData(Request $request)
    {
        $user = $request->user()->load('role:id,name');

        return response()->json([
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'full_name' => $user->full_name,
                'role' => $user->role ? ['id' => $user->role->id, 'name' => $user->role->name] : null,
                'status' => $user->status,
                'last_login' => $user->last_login
            ]
        ]);
    }

    /**
     * Write login log directly
     */
    private function writeLoginLog(Request $request, $userId, $email, $status, $details)
    {
        try {
            LoginLog::create([
                'user_id' => $userId,
                'action' => 'login',
                'email_attempted' => $email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => $status,
                'details' => $details,
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to write login log: ' . $e->getMessage());
        }
    }
}