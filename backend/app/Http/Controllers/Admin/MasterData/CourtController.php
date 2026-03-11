<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Court;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CourtController extends Controller
{
    /**
     * Get all courts with optional filters
     */
    public function index(Request $request)
    {
        try {
            $query = Court::query();

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where('name', 'like', "%{$search}%");
            }

            // Filter by type
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            // Filter by active status
            if ($request->filled('is_active')) {
                $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
            }

            // Sorting - put "Others" at the end always
            $sortField = $request->get('sort_by', 'sort_order');
            $sortDirection = $request->get('sort_direction', 'asc');
            
            if ($sortField === 'sort_order') {
                // Custom sorting: normal items first (sort_order < 9000), then Others (sort_order >= 9000)
                $query->orderByRaw('CASE WHEN sort_order >= 9000 THEN 1 ELSE 0 END')
                      ->orderBy('sort_order', $sortDirection);
            } else {
                $allowedSorts = ['name', 'type', 'is_active', 'created_at'];
                $sortField = in_array($sortField, $allowedSorts) ? $sortField : 'sort_order';
                $query->orderBy($sortField, $sortDirection);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $courts = $query->paginate($perPage);

            return response()->json([
                'data' => $courts->items(),
                'meta' => [
                    'current_page' => $courts->currentPage(),
                    'last_page' => $courts->lastPage(),
                    'per_page' => $courts->perPage(),
                    'total' => $courts->total(),
                    'from' => $courts->firstItem(),
                    'to' => $courts->lastItem(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch courts',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Get active courts for dropdown
     */
    public function getActive()
    {
        try {
            $courts = Court::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'type', 'address']);

            return response()->json(['data' => $courts]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch courts',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Get court types for dropdown
     */
    public function getTypes()
    {
        return response()->json(['data' => Court::getTypes()]);
    }

    /**
     * Get single court
     */
    public function show($id)
    {
        try {
            $court = Court::findOrFail($id);
            return response()->json(['data' => $court]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Court not found',
                'errors' => ['id' => ['Court not found']]
            ], 404);
        }
    }

    /**
     * Create new court
     */
/**
 * Create new court
 */
public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'type' => 'nullable|in:' . implode(',', Court::getTypes()),
        'address' => 'nullable|string|max:500',
        'contact_info' => 'nullable|string|max:500',
        'sort_order' => 'nullable|integer|min:0',
        'is_active' => 'nullable|boolean',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        DB::beginTransaction();

        // Check if this is "Others" court
        $isOthers = strtolower($request->name) === 'others';
        
        if ($isOthers) {
            // Force "Others" to always be at the end
            $sortOrder = 9999;
            
            // Delete any existing "Others" court
            Court::where('name', 'LIKE', '%others%')
                ->orWhere('name', 'LIKE', '%Others%')
                ->delete();
        } else {
            // For normal courts, if sort_order is provided, use it and shift others
            if ($request->has('sort_order') && $request->sort_order !== null) {
                $sortOrder = $request->sort_order;
                
                // Check if the provided sort_order is already taken
                $existing = Court::where('sort_order', $sortOrder)
                    ->where('id', '!=', $request->id ?? 0)
                    ->first();
                    
                if ($existing) {
                    // Shift all courts with sort_order >= requested order up by 1
                    Court::where('sort_order', '>=', $sortOrder)
                        ->where('sort_order', '<', 9000)
                        ->increment('sort_order');
                }
            } else {
                // Get the next available number (max + 1)
                $maxSortOrder = Court::where('sort_order', '<', 9000)->max('sort_order');
                $sortOrder = $maxSortOrder ? $maxSortOrder + 1 : 1;
            }
        }

        $court = Court::create([
            'name' => trim($request->name),
            'type' => $request->type ?? 'Court',
            'address' => $request->address,
            'contact_info' => $request->contact_info,
            'sort_order' => $sortOrder,
            'is_active' => $request->is_active ?? true,
        ]);

        DB::commit();

        return response()->json([
            'message' => 'Court created successfully',
            'data' => $court
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Failed to create court',
            'errors' => ['server' => [$e->getMessage()]]
        ], 500);
    }
}

    /**
     * Update court
     */
    public function update(Request $request, $id)
    {
        try {
            $court = Court::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'type' => 'nullable|in:' . implode(',', Court::getTypes()),
                'address' => 'nullable|string|max:500',
                'contact_info' => 'nullable|string|max:500',
                'sort_order' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Check if this is "Others" court
            $isOthers = strtolower($request->name ?? $court->name) === 'others';
            
            if ($isOthers) {
                // Force "Others" to always be at the end
                $sortOrder = 9999;
            } else {
                $sortOrder = $request->sort_order ?? $court->sort_order;
                
                // If sort_order changed and it's not "Others"
                if ($request->has('sort_order') && $request->sort_order != $court->sort_order) {
                    // Remove the old sort order from the pool
                    Court::where('sort_order', '>', $court->sort_order)
                        ->where('sort_order', '<', 9000)
                        ->decrement('sort_order');
                    
                    // Make room for new sort order if needed
                    Court::where('sort_order', '>=', $request->sort_order)
                        ->where('sort_order', '<', 9000)
                        ->increment('sort_order');
                }
            }

            $court->update([
                'name' => $request->name ?? $court->name,
                'type' => $request->type ?? $court->type,
                'address' => $request->address ?? $court->address,
                'contact_info' => $request->contact_info ?? $court->contact_info,
                'sort_order' => $sortOrder,
                'is_active' => $request->is_active ?? $court->is_active,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Court updated successfully',
                'data' => $court->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update court',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Toggle court active status
     */
    public function toggleActive($id)
    {
        try {
            $court = Court::findOrFail($id);
            $court->update(['is_active' => !$court->is_active]);

            return response()->json([
                'message' => $court->is_active ? 'Court activated' : 'Court deactivated',
                'data' => $court
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to toggle court status',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Delete court
     */
    public function destroy($id)
    {
        try {
            $court = Court::findOrFail($id);

            // Don't allow deleting "Others" court
            if (strtolower($court->name) === 'others') {
                return response()->json([
                    'message' => 'Cannot delete "Others" court',
                    'errors' => ['court' => ['The "Others" court cannot be deleted']]
                ], 422);
            }

            // Check if court has cases
            if ($court->cases()->count() > 0) {
                return response()->json([
                    'message' => 'Cannot delete court with existing cases',
                    'errors' => ['court' => ['This court is being used by existing cases']]
                ], 422);
            }

            DB::beginTransaction();

            // Reorder remaining courts
            Court::where('sort_order', '>', $court->sort_order)
                ->where('sort_order', '<', 9000)
                ->decrement('sort_order');

            $court->delete();

            DB::commit();

            return response()->json([
                'message' => 'Court deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete court',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }
}