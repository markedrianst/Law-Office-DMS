<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rules\Password;

class UserManagementController extends Controller
{
    /**
     * Get available roles - Cached for 1 hour
     */
    public function getRoles()
    {
        try {
            $roles = Cache::remember('user_roles', 3600, function() {
                return DB::table('roles')
                    ->select('id', 'name')
                    ->whereIn('name', ['lawyer', 'clerk'])
                    ->orderBy('name')
                    ->get()
                    ->map(function ($role) {
                        return [
                            'id' => $role->id,
                            'name' => ucfirst($role->name),
                        ];
                    });
            });

            return response()->json(['data' => $roles]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch roles',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Display a listing of users - Fixed: Removed address and contact_number
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 5);
            $search = $request->get('search');
            $role = $request->get('role');
            $sortField = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');

            // Map sort fields to database columns
            $fieldMap = [
                'name' => 'u.full_name',
                'email' => 'u.email',
                'role' => 'r.name',
                'status' => 'u.status',
                'last_login' => 'u.last_login',
                'created_at' => 'u.created_at',
            ];
            $orderBy = $fieldMap[$sortField] ?? 'u.created_at';

            // Build the query with proper joins and pagination - REMOVED address and contact_number
            $users = DB::table('users as u')
                ->join('roles as r', 'r.id', '=', 'u.role_id')
                ->whereIn('r.name', ['lawyer', 'clerk'])
                ->when($role, function($query, $role) {
                    return $query->where('r.name', strtolower($role));
                })
                ->when($search, function($query, $search) {
                    return $query->where(function($q) use ($search) {
                        $q->where('u.full_name', 'like', "%{$search}%")
                          ->orWhere('u.email', 'like', "%{$search}%");
                    });
                })
                ->select(
                    'u.id',
                    'u.full_name as name',
                    'u.email',
                    'u.status',
                    'u.created_at',
                    'u.last_login',
                    'r.name as role'
                )
                ->orderBy($orderBy, $sortDirection)
                ->paginate($perPage);

            // Transform the data - removed address and contact_number
            $transformedUsers = collect($users->items())->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => ucfirst($user->role),
                    'status' => ucfirst($user->status),
                    'created_at' => $user->created_at,
                    'last_login' => $user->last_login,
                ];
            });

            return response()->json([
                'data' => $transformedUsers,
                'meta' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch users',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Store a newly created user - Fixed: Removed address and contact_number
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:50',
            'middleName' => 'nullable|string|max:50',
            'lastName' => 'required|string|max:50',
            'email' => 'required|string|email|max:255|unique:users,email',
            'role' => 'required|in:Lawyer,Clerk',
            'password' => ['required', 'string', Password::min(6)],
            'status' => 'required|in:Active,Inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $fullName = trim($request->firstName . ' ' . $request->middleName . ' ' . $request->lastName);
            $fullName = preg_replace('/\s+/', ' ', $fullName);
            
            $roleId = DB::table('roles')->where('name', strtolower($request->role))->value('id');

            if (!$roleId) {
                return response()->json([
                    'message' => 'Invalid role selected',
                    'errors' => ['role' => ['The selected role is invalid']]
                ], 422);
            }

            $userId = DB::table('users')->insertGetId([
                'role_id' => $roleId,
                'full_name' => $fullName,
                'email' => $request->email,
                'password_hash' => Hash::make($request->password),
                'status' => strtolower($request->status),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Get the created user - removed address and contact_number
            $user = DB::table('users as u')
                ->join('roles as r', 'r.id', '=', 'u.role_id')
                ->where('u.id', $userId)
                ->select('u.id', 'u.full_name as name', 'u.email', 'u.status', 'u.created_at', 'u.last_login', 'r.name as role')
                ->first();

            DB::commit();

            $this->logUserAction($request, 'user_create', 'success', [
                'user_id' => $userId,
                'email' => $request->email,
                'name' => $fullName,
                'role' => $request->role
            ]);

            return response()->json([
                'message' => 'User created successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => ucfirst($user->role),
                    'status' => ucfirst($user->status),
                    'created_at' => $user->created_at,
                    'last_login' => $user->last_login,
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logUserAction($request, 'user_create', 'failed', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Failed to create user',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Update the specified user - Fixed: Removed address and contact_number
     */
    public function update(Request $request, $id)
    {
        try {
            $user = DB::table('users')->where('id', $id)->first();

            if (!$user) {
                return response()->json([
                    'message' => 'User not found',
                    'errors' => ['id' => ['User not found']]
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'firstName' => 'sometimes|required|string|max:50',
                'middleName' => 'nullable|string|max:50',
                'lastName' => 'sometimes|required|string|max:50',
                'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
                'role' => 'sometimes|required|in:Lawyer,Clerk',
                'password' => ['nullable', 'string', Password::min(6)],
                'status' => 'sometimes|required|in:Active,Inactive',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $updateData = [];
            $changes = [];

            // Update name if provided
            if ($request->has('firstName') || $request->has('lastName')) {
                $currentName = $user->full_name;
                $parts = explode(' ', trim($currentName));
                
                $firstName = $request->get('firstName', $parts[0] ?? '');
                $middleName = $request->get('middleName', count($parts) > 2 ? implode(' ', array_slice($parts, 1, -1)) : '');
                $lastName = $request->get('lastName', count($parts) > 1 ? end($parts) : '');
                
                $fullName = trim($firstName . ' ' . $middleName . ' ' . $lastName);
                $fullName = preg_replace('/\s+/', ' ', $fullName);
                $updateData['full_name'] = $fullName;
                $changes[] = 'name';
            }

            // Update email
            if ($request->has('email') && $request->email !== $user->email) {
                $updateData['email'] = $request->email;
                $changes[] = 'email';
            }

            // Update role
            if ($request->has('role')) {
                $roleId = DB::table('roles')->where('name', strtolower($request->role))->value('id');
                if ($roleId && $roleId != $user->role_id) {
                    $updateData['role_id'] = $roleId;
                    $changes[] = 'role';
                }
            }

            // Update password
            if ($request->filled('password')) {
                $updateData['password_hash'] = Hash::make($request->password);
                $updateData['must_change_password'] = true;
                $changes[] = 'password';
                
                // Delete all tokens for security
                DB::table('personal_access_tokens')
                    ->where('tokenable_id', $id)
                    ->where('tokenable_type', 'App\Models\User')
                    ->delete();
            }

            // Update status
            if ($request->has('status')) {
                $updateData['status'] = strtolower($request->status);
                $changes[] = 'status';
            }

            if (!empty($updateData)) {
                $updateData['updated_at'] = now();
                DB::table('users')->where('id', $id)->update($updateData);
            }

            // Get updated user - removed address and contact_number
            $updatedUser = DB::table('users as u')
                ->join('roles as r', 'r.id', '=', 'u.role_id')
                ->where('u.id', $id)
                ->select('u.id', 'u.full_name as name', 'u.email', 'u.status', 'u.created_at', 'u.last_login', 'r.name as role')
                ->first();

            DB::commit();

            $this->logUserAction($request, 'user_update', 'success', [
                'user_id' => $id,
                'email' => $updatedUser->email,
                'changes' => $changes
            ]);

            return response()->json([
                'message' => 'User updated successfully',
                'data' => [
                    'id' => $updatedUser->id,
                    'name' => $updatedUser->name,
                    'email' => $updatedUser->email,
                    'role' => ucfirst($updatedUser->role),
                    'status' => ucfirst($updatedUser->status),
                    'created_at' => $updatedUser->created_at,
                    'last_login' => $updatedUser->last_login,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update user',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Remove the specified user
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = DB::table('users')->where('id', $id)->first();

            if (!$user) {
                return response()->json([
                    'message' => 'User not found',
                    'errors' => ['id' => ['User not found']]
                ], 404);
            }

            DB::beginTransaction();

            // Delete tokens
            DB::table('personal_access_tokens')
                ->where('tokenable_id', $id)
                ->where('tokenable_type', 'App\Models\User')
                ->delete();

            // Delete user
            DB::table('users')->where('id', $id)->delete();

            DB::commit();

            $this->logUserAction($request, 'user_delete', 'success', [
                'email' => $user->email,
                'name' => $user->full_name
            ]);

            return response()->json([
                'message' => 'User deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete user',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Toggle user status
     */
    public function toggleStatus(Request $request, $id)
    {
        try {
            $user = DB::table('users')->where('id', $id)->first();
            
            if (!$user) {
                return response()->json([
                    'message' => 'User not found',
                    'errors' => ['id' => ['User not found']]
                ], 404);
            }

            $newStatus = $user->status === 'active' ? 'inactive' : 'active';
            
            DB::table('users')->where('id', $id)->update([
                'status' => $newStatus,
                'updated_at' => now()
            ]);

            $this->logUserAction($request, 'user_toggle_status', 'success', [
                'user_id' => $id,
                'email' => $user->email,
                'new_status' => $newStatus
            ]);

            return response()->json([
                'message' => 'User status updated successfully',
                'data' => [
                    'id' => (int)$id,
                    'status' => ucfirst($newStatus)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to toggle user status',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Helper: Log user actions
     */
    private function logUserAction(Request $request, $action, $status, $details)
    {
        try {
            DB::table('login_logs')->insert([
                'user_id' => $request->user()?->id,
                'action' => $action,
                'email_attempted' => $details['email'] ?? 'system',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => $status,
                'details' => json_encode($details),
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to write log: ' . $e->getMessage());
        }
    }
}