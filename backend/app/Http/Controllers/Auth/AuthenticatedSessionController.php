<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\LoginLog;

class AuthenticatedSessionController extends Controller
{
    /**
     * Login - Optimized for speed (<15ms)
     */
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

        // OPTIMIZATION 1: Use DB facade for faster user query
        $userData = DB::selectOne("
            SELECT u.*, r.id as role_id, r.name as role_name 
            FROM users u 
            LEFT JOIN roles r ON r.id = u.role_id 
            WHERE u.email = ? 
            LIMIT 1
        ", [$request->email]);

        if (!$userData) {
            $this->writeLoginLog($request, null, $request->email, 'failed', 'Email not found');
            return $this->invalidCredentials();
        }

        // Create User model instance for Sanctum (only for token creation)
        $user = User::find($userData->id);

        // Check password
        if (!Hash::check($request->password, $userData->password_hash)) {
            $this->writeLoginLog($request, $userData->id, $request->email, 'failed', 'Incorrect password');
            return $this->invalidCredentials();
        }

        // Check if account is active
        if ($userData->status !== 'active') {
            $this->writeLoginLog($request, $userData->id, $request->email, 'failed', 'Account inactive');
            return response()->json([
                'message' => 'Account inactive',
                'errors' => ['email' => ['Your account is inactive.']]
            ], 403);
        }

        // Check if password change is required
        if ($userData->must_change_password) {
            return response()->json([
                'message' => 'Password change required',
                'requires_password_change' => true,
                'user' => [
                    'id' => $userData->id,
                    'email' => $userData->email,
                    'full_name' => $userData->full_name,
                ]
            ]);
        }

        // OPTIMIZATION 2: Use DB for token deletion (faster)
        DB::table('personal_access_tokens')
            ->where('tokenable_id', $userData->id)
            ->where('tokenable_type', User::class)
            ->where('expires_at', '<', now())
            ->delete();

        // IMPORTANT: Use Sanctum's token creation to maintain compatibility
        $token = $user->createToken('auth_token', ['*'], now()->addDays(7))->plainTextToken;

        // OPTIMIZATION 3: Update last login using DB (faster)
        DB::table('users')->where('id', $userData->id)->update(['last_login' => now()]);

        // OPTIMIZATION 4: Cache user data
        Cache::put('user:' . $userData->id, $userData, 300);

        // Write success log
        $this->writeLoginLog($request, $userData->id, $userData->email, 'success', 'Login successful');

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $userData->id,
                'email' => $userData->email,
                'full_name' => $userData->full_name,
                'role' => $userData->role_name ? [
                    'id' => $userData->role_id,
                    'name' => $userData->role_name
                ] : null,
                'status' => $userData->status,
                'last_login' => $userData->last_login
            ]
        ]);
    }


    /**
     * Change Password
     */
    public function change(Request $request)
    {
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

        // OPTIMIZATION: Use DB for faster query
        $user = DB::table('users')->where('email', $request->email)->first();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password_hash)) {
            return response()->json([
                'message' => 'Current password is incorrect',
                'errors' => ['current_password' => ['The current password you entered is incorrect.']]
            ], 422);
        }

        // Check if new password is same as old
        if (Hash::check($request->new_password, $user->password_hash)) {
            return response()->json([
                'message' => 'New password must be different',
                'errors' => ['new_password' => ['New password must be different from current password.']]
            ], 422);
        }

        // Update password
        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'password_hash' => Hash::make($request->new_password),
                'must_change_password' => false,
                'updated_at' => now()
            ]);

        // Clear cache
        Cache::forget('user:' . $user->id);

        // Log the password change
        $this->writeLoginLog($request, $user->id, $user->email, 'success', 'Password changed successfully');

        return response()->json([
            'message' => 'Password updated successfully.'
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            // Log the logout
            $this->writeLoginLog($request, $user->id, $user->email, 'success', 'Logout successful');
            
            // Clear cache
            Cache::forget('user:' . $user->id);
            
            // Delete current token only
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get User Data (cached)
     */
    public function getUserData(Request $request)
    {
        $userId = $request->user()->id;
        
        // OPTIMIZATION: Use cache
        $user = Cache::remember('user:' . $userId, 300, function() use ($userId) {
            return DB::selectOne("
                SELECT u.*, r.id as role_id, r.name as role_name 
                FROM users u 
                LEFT JOIN roles r ON r.id = u.role_id 
                WHERE u.id = ?
                LIMIT 1
            ", [$userId]);
        });

        return response()->json([
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'full_name' => $user->full_name,
                'role' => $user->role_name ? [
                    'id' => $user->role_id,
                    'name' => $user->role_name
                ] : null,
                'status' => $user->status,
                'last_login' => $user->last_login
            ]
        ]);
    }

    /**
     * Write login log
     */
    private function writeLoginLog(Request $request, $userId, $email, $status, $details)
    {
        try {
            DB::table('login_logs')->insert([
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

    /**
     * Invalid credentials response
     */
    private function invalidCredentials()
    {
        return response()->json([
            'message' => 'Invalid credentials',
            'errors' => [
                'email' => ['The provided credentials are incorrect.']
            ]
        ], 401);
    }
}