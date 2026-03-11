<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    /**
     * Get all clients with optional filters
     */
    public function index(Request $request)
    {
        try {
            $query = Client::query();

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where('full_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('contact_no', 'like', "%{$search}%");
            }

            // Sorting
            $sortField = $request->get('sort_by', 'full_name');
            $sortDirection = $request->get('sort_direction', 'asc');
            $allowedSorts = ['full_name', 'created_at', 'email'];
            $sortField = in_array($sortField, $allowedSorts) ? $sortField : 'full_name';
            
            $query->orderBy($sortField, $sortDirection);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $clients = $query->paginate($perPage);

            return response()->json([
                'data' => $clients->items(),
                'meta' => [
                    'current_page' => $clients->currentPage(),
                    'last_page' => $clients->lastPage(),
                    'per_page' => $clients->perPage(),
                    'total' => $clients->total(),
                    'from' => $clients->firstItem(),
                    'to' => $clients->lastItem(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch clients',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Search clients for dropdown
     */
    public function search(Request $request)
    {
        try {
            $query = Client::query();

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where('full_name', 'like', "%{$search}%");
            }

            $limit = $request->get('limit', 50);
            $clients = $query->orderBy('full_name')
                ->limit($limit)
                ->get(['id', 'full_name', 'email', 'contact_no']);

            return response()->json(['data' => $clients]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to search clients',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Get single client
     */
    public function show($id)
    {
        try {
            $client = Client::findOrFail($id);
            return response()->json(['data' => $client]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Client not found',
                'errors' => ['id' => ['Client not found']]
            ], 404);
        }
    }

    /**
     * Create new client
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'contact_no' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:clients,email',
            'address' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $client = Client::create([
                'full_name' => $request->full_name,
                'contact_no' => $request->contact_no,
                'email' => $request->email,
                'address' => $request->address,
            ]);

            return response()->json([
                'message' => 'Client created successfully',
                'data' => $client
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create client',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Update client
     */
    public function update(Request $request, $id)
    {
        try {
            $client = Client::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'full_name' => 'sometimes|required|string|max:255',
                'contact_no' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255|unique:clients,email,' . $id,
                'address' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $client->update([
                'full_name' => $request->full_name ?? $client->full_name,
                'contact_no' => $request->contact_no ?? $client->contact_no,
                'email' => $request->email ?? $client->email,
                'address' => $request->address ?? $client->address,
            ]);

            return response()->json([
                'message' => 'Client updated successfully',
                'data' => $client->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update client',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Delete client
     */
    public function destroy($id)
    {
        try {
            $client = Client::findOrFail($id);

            // Check if client has cases
            if ($client->cases()->count() > 0) {
                return response()->json([
                    'message' => 'Cannot delete client with existing cases',
                    'errors' => ['client' => ['This client has associated cases']]
                ], 422);
            }

            $client->delete();

            return response()->json([
                'message' => 'Client deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete client',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Get client cases
     */
    public function getCases($id)
    {
        try {
            $client = Client::findOrFail($id);
            
            $cases = $client->cases()
                ->with(['category', 'currentStage', 'lawyer', 'clerk'])
                ->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 15));

            return response()->json([
                'data' => $cases->items(),
                'meta' => [
                    'current_page' => $cases->currentPage(),
                    'last_page' => $cases->lastPage(),
                    'per_page' => $cases->perPage(),
                    'total' => $cases->total(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch client cases',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }
}