<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\LoginLog;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class UserManagementController extends Controller
{
    /**
     * Get available roles for dropdown
     */
    public function getRoles()
    {
        try {
            $roles = Role::select('id', 'name')
                ->whereIn('name', ['lawyer', 'clerk'])
                ->orderBy('name')
                ->get()
                ->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => ucfirst($role->name),
                    ];
                });

            return response()->json([
                'data' => $roles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch roles',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        try {
            $query = User::with('role');

            // Filter by role
            if ($request->filled('role')) {
                $query->whereHas('role', function ($q) use ($request) {
                    $q->where('name', strtolower($request->role));
                });
            } else {
                $query->whereHas('role', function ($q) {
                    $q->whereIn('name', ['lawyer', 'clerk']);
                });
            }

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Sorting
            $sortField = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            
            $fieldMap = [
                'name' => 'full_name',
                'email' => 'email',
                'role' => 'role_id',
                'status' => 'status',
                'last_login' => 'last_login',
                'created_at' => 'created_at',
            ];
            
            $dbField = $fieldMap[$sortField] ?? 'created_at';
            $query->orderBy($dbField, $sortDirection);

            // Pagination
            $perPage = $request->get('per_page', 10);
            $users = $query->paginate($perPage);

            $transformedUsers = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->email,
                    'role' => ucfirst($user->role->name),
                    'status' => ucfirst($user->status),
                    'created_at' => $user->created_at,
                    'last_login' => $user->last_login,
                    'address' => $user->address ?? '',
                    'contact_number' => $user->contact_number ?? '',
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
     * Store a newly created user
     */
    public function store(Request $request)
    {
        // Validate all inputs
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:50',
            'middleName' => 'nullable|string|max:50',
            'lastName' => 'required|string|max:50',
            'address' => 'nullable|string',
            'contact' => 'nullable|string|max:13',
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
            // Combine name fields
            $fullName = trim($request->firstName . ' ' . $request->middleName . ' ' . $request->lastName);
            $fullName = preg_replace('/\s+/', ' ', $fullName); // Remove extra spaces
            
            // Get role ID
            $roleName = strtolower($request->role);
            $roleId = Role::where('name', $roleName)->value('id');

            if (!$roleId) {
                return response()->json([
                    'message' => 'Invalid role selected',
                    'errors' => ['role' => ['The selected role is invalid']]
                ], 422);
            }

            // Create user
            $user = User::create([
                'role_id' => $roleId,
                'full_name' => $fullName,
                'email' => $request->email,
                'password_hash' => Hash::make($request->password),
                'status' => strtolower($request->status),
                'address' => $request->address,
                'contact_number' => $request->contact ? preg_replace('/\D/', '', $request->contact) : null,
            ]);

            // Log the action
            $this->logUserAction($request, 'user_create', 'success', [
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->full_name,
                'role' => $request->role
            ]);

            return response()->json([
                'message' => 'User created successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->email,
                    'role' => ucfirst($user->role->name),
                    'status' => ucfirst($user->status),
                    'created_at' => $user->created_at,
                    'last_login' => $user->last_login,
                    'address' => $user->address ?? '',
                    'contact_number' => $user->contact_number ?? '',
                ],
            ], 201);

        } catch (\Exception $e) {
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
     * Display the specified user
     */
    public function show(Request $request, $id)
    {
        try {
            $user = User::with('role')->findOrFail($id);

            return response()->json([
                'data' => [
                    'id' => $user->id,
                    'firstName' => $this->extractFirstName($user->full_name),
                    'middleName' => $this->extractMiddleName($user->full_name),
                    'lastName' => $this->extractLastName($user->full_name),
                    'email' => $user->email,
                    'role' => ucfirst($user->role->name),
                    'status' => ucfirst($user->status),
                    'address' => $user->address ?? '',
                    'contact' => $user->contact_number ?? '',
                    'created_at' => $user->created_at,
                    'last_login' => $user->last_login,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'User not found',
                'errors' => ['id' => ['User not found']]
            ], 404);
        }
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::with('role')->findOrFail($id);

            // Validate inputs
            $validator = Validator::make($request->all(), [
                'firstName' => 'sometimes|required|string|max:50',
                'middleName' => 'nullable|string|max:50',
                'lastName' => 'sometimes|required|string|max:50',
                'address' => 'nullable|string',
                'contact' => 'nullable|string|max:13',
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

            $updateData = [];

            // Update name if provided
            if ($request->has('firstName') || $request->has('lastName')) {
                $firstName = $request->get('firstName', $this->extractFirstName($user->full_name));
                $middleName = $request->get('middleName', $this->extractMiddleName($user->full_name));
                $lastName = $request->get('lastName', $this->extractLastName($user->full_name));
                
                $fullName = trim($firstName . ' ' . $middleName . ' ' . $lastName);
                $fullName = preg_replace('/\s+/', ' ', $fullName);
                $updateData['full_name'] = $fullName;
            }

            // Update email
            if ($request->has('email') && $request->email !== $user->email) {
                $updateData['email'] = $request->email;
            }

            // Update role
            if ($request->has('role')) {
                $roleName = strtolower($request->role);
                $roleId = Role::where('name', $roleName)->value('id');
                if ($roleId) {
                    $updateData['role_id'] = $roleId;
                }
            }

            // Update password
            if ($request->filled('password')) {
                $updateData['password_hash'] = Hash::make($request->password);
                $updateData['must_change_password'] = true;
                // Revoke all tokens for security
                $user->tokens()->delete();
            }

            // Update address
            if ($request->has('address')) {
                $updateData['address'] = $request->address;
            }

            // Update contact
            if ($request->has('contact')) {
                $updateData['contact_number'] = $request->contact ? preg_replace('/\D/', '', $request->contact) : null;
            }

            // Update status
            if ($request->has('status')) {
                $updateData['status'] = strtolower($request->status);
            }

            if (!empty($updateData)) {
                $user->update($updateData);
                $user->load('role');

                $this->logUserAction($request, 'user_update', 'success', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'changes' => array_keys($updateData)
                ]);
            }

            return response()->json([
                'message' => 'User updated successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->email,
                    'role' => ucfirst($user->role->name),
                    'status' => ucfirst($user->status),
                    'created_at' => $user->created_at,
                    'last_login' => $user->last_login,
                    'address' => $user->address ?? '',
                    'contact_number' => $user->contact_number ?? '',
                ],
            ]);

        } catch (\Exception $e) {
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
            $user = User::findOrFail($id);

            // Store info for logging
            $userEmail = $user->email;
            $userName = $user->full_name;

            // Revoke all tokens
            $user->tokens()->delete();

            // Delete user
            $user->delete();

            $this->logUserAction($request, 'user_delete', 'success', [
                'email' => $userEmail,
                'name' => $userName
            ]);

            return response()->json([
                'message' => 'User deleted successfully'
            ]);

        } catch (\Exception $e) {
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
            $user = User::findOrFail($id);
            $newStatus = $user->status === 'active' ? 'inactive' : 'active';
            
            $user->update(['status' => $newStatus]);

            $this->logUserAction($request, 'user_toggle_status', 'success', [
                'user_id' => $user->id,
                'email' => $user->email,
                'new_status' => $newStatus
            ]);

            return response()->json([
                'message' => 'User status updated successfully',
                'data' => [
                    'id' => $user->id,
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
            LoginLog::create([
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

    /**
     * Helper: Extract name parts
     */
    private function extractFirstName($fullName)
    {
        $parts = explode(' ', trim($fullName));
        return $parts[0] ?? '';
    }

    private function extractLastName($fullName)
    {
        $parts = explode(' ', trim($fullName));
        return count($parts) > 1 ? end($parts) : '';
    }

    private function extractMiddleName($fullName)
    {
        $parts = explode(' ', trim($fullName));
        if (count($parts) > 2) {
            return implode(' ', array_slice($parts, 1, -1));
        }
        return '';
    }
}