<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\CaseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CaseCategoryController extends Controller
{
    /**
     * Get all case categories with optional filters
     */
    public function index(Request $request)
    {
        try {
            $query = CaseCategory::query();

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where('name', 'like', "%{$search}%");
            }

            // Filter by active status
            if ($request->filled('is_active')) {
                $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
            }

            // Sorting - put "Other" at the end always
            $sortField = $request->get('sort_by', 'sort_order');
            $sortDirection = $request->get('sort_direction', 'asc');
            
            if ($sortField === 'sort_order') {
                // Custom sorting: normal items first (sort_order < 9000), then Other (sort_order >= 9000)
                $query->orderByRaw('CASE WHEN sort_order >= 9000 THEN 1 ELSE 0 END')
                      ->orderBy('sort_order', $sortDirection);
            } else {
                $allowedSorts = ['name', 'is_active', 'created_at'];
                $sortField = in_array($sortField, $allowedSorts) ? $sortField : 'sort_order';
                $query->orderBy($sortField, $sortDirection);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $categories = $query->paginate($perPage);

            return response()->json([
                'data' => $categories->items(),
                'meta' => [
                    'current_page' => $categories->currentPage(),
                    'last_page' => $categories->lastPage(),
                    'per_page' => $categories->perPage(),
                    'total' => $categories->total(),
                    'from' => $categories->firstItem(),
                    'to' => $categories->lastItem(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch categories',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Get active categories for dropdown
     */
    public function getActive()
    {
        try {
            $categories = CaseCategory::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'color']);

            return response()->json(['data' => $categories]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch categories',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Get single category
     */
    public function show($id)
    {
        try {
            $category = CaseCategory::findOrFail($id);
            return response()->json(['data' => $category]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Category not found',
                'errors' => ['id' => ['Category not found']]
            ], 404);
        }
    }

    /**
     * Create new category
     *//**
 * Create new category
 */
public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255|unique:case_categories,name',
        'color' => 'nullable|string|max:7|regex:/^#[a-fA-F0-9]{6}$/',
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

        // Check if this is "Other" category
        $isOther = strtolower($request->name) === 'other';
        
        if ($isOther) {
            // Force "Other" to always be at the end
            $sortOrder = 9999;
            
            // Delete any existing "Other" category
            CaseCategory::where('name', 'LIKE', '%other%')
                ->orWhere('name', 'LIKE', '%Other%')
                ->delete();
        } else {
            // For normal categories, if sort_order is provided, use it and shift others
            if ($request->has('sort_order') && $request->sort_order !== null) {
                $sortOrder = $request->sort_order;
                
                // Shift all categories with sort_order >= requested order up by 1
                CaseCategory::where('sort_order', '>=', $sortOrder)
                    ->where('sort_order', '<', 9000)
                    ->increment('sort_order');
            } else {
                // Get the next available number (max + 1)
                $maxSortOrder = CaseCategory::where('sort_order', '<', 9000)->max('sort_order');
                $sortOrder = $maxSortOrder ? $maxSortOrder + 1 : 1;
            }
        }

        $category = CaseCategory::create([
            'name' => $request->name,
            'color' => $request->color ?? '#1a4972',
            'sort_order' => $sortOrder,
            'is_active' => $request->is_active ?? true,
        ]);

        DB::commit();

        return response()->json([
            'message' => 'Category created successfully',
            'data' => $category
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Failed to create category',
            'errors' => ['server' => [$e->getMessage()]]
        ], 500);
    }
}

    /**
     * Update category
     */
    public function update(Request $request, $id)
    {
        try {
            $category = CaseCategory::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255|unique:case_categories,name,' . $id,
                'color' => 'nullable|string|max:7|regex:/^#[a-fA-F0-9]{6}$/',
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

            // Check if this is "Other" category
            $isOther = strtolower($request->name ?? $category->name) === 'other';
            
            if ($isOther) {
                // Force "Other" to always be at the end
                $sortOrder = 9999;
            } else {
                $sortOrder = $request->sort_order ?? $category->sort_order;
                
                // If sort_order changed and it's not "Other"
                if ($request->has('sort_order') && $request->sort_order != $category->sort_order) {
                    // Remove the old sort order from the pool
                    CaseCategory::where('sort_order', '>', $category->sort_order)
                        ->where('sort_order', '<', 9000)
                        ->decrement('sort_order');
                    
                    // Make room for new sort order if needed
                    CaseCategory::where('sort_order', '>=', $request->sort_order)
                        ->where('sort_order', '<', 9000)
                        ->increment('sort_order');
                }
            }

            $category->update([
                'name' => $request->name ?? $category->name,
                'color' => $request->color ?? $category->color,
                'sort_order' => $sortOrder,
                'is_active' => $request->is_active ?? $category->is_active,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Category updated successfully',
                'data' => $category->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update category',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Toggle category active status
     */
    public function toggleActive($id)
    {
        try {
            $category = CaseCategory::findOrFail($id);
            $category->update(['is_active' => !$category->is_active]);

            return response()->json([
                'message' => $category->is_active ? 'Category activated' : 'Category deactivated',
                'data' => $category
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to toggle category status',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Delete category
     */
    public function destroy($id)
    {
        try {
            $category = CaseCategory::findOrFail($id);

            // Don't allow deleting "Other" category
            if (strtolower($category->name) === 'other') {
                return response()->json([
                    'message' => 'Cannot delete "Other" category',
                    'errors' => ['category' => ['The "Other" category cannot be deleted']]
                ], 422);
            }

            // Check if category has cases
            if ($category->cases()->count() > 0) {
                return response()->json([
                    'message' => 'Cannot delete category with existing cases',
                    'errors' => ['category' => ['This category is being used by existing cases']]
                ], 422);
            }

            DB::beginTransaction();

            // Reorder remaining categories
            CaseCategory::where('sort_order', '>', $category->sort_order)
                ->where('sort_order', '<', 9000)
                ->decrement('sort_order');

            $category->delete();

            DB::commit();

            return response()->json([
                'message' => 'Category deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete category',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }
}