<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentApproval;
use App\Models\CaseActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class DocumentController extends Controller
{
    /**
     * Get all documents with optional filters
     */
    public function index(Request $request)
    {
        try {
            $query = Document::query();

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where('type', 'like', "%{$search}%");
            }

            // Filter by category
            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            // Filter by approval status
            if ($request->filled('approval_status')) {
                $query->where('approval_status', $request->approval_status);
            }

            // Filter by active status
            if ($request->filled('is_active')) {
                $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
            }

            // Filter by requires_approval
            if ($request->filled('requires_approval')) {
                $query->where('requires_approval', filter_var($request->requires_approval, FILTER_VALIDATE_BOOLEAN));
            }

            // Sorting - put "Others" at the end always
            $sortField = $request->get('sort_by', 'sort_order');
            $sortDirection = $request->get('sort_direction', 'asc');
            
            if ($sortField === 'sort_order') {
                // Custom sorting: normal items first (sort_order < 9000), then Others (sort_order >= 9000)
                $query->orderByRaw('CASE WHEN sort_order >= 9000 THEN 1 ELSE 0 END')
                      ->orderBy('sort_order', $sortDirection);
            } else {
                $allowedSorts = ['type', 'category', 'requires_approval', 'is_active', 'created_at'];
                $sortField = in_array($sortField, $allowedSorts) ? $sortField : 'sort_order';
                $query->orderBy($sortField, $sortDirection);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $documents = $query->paginate($perPage);

            return response()->json([
                'data' => $documents->items(),
                'meta' => [
                    'current_page' => $documents->currentPage(),
                    'last_page' => $documents->lastPage(),
                    'per_page' => $documents->perPage(),
                    'total' => $documents->total(),
                    'from' => $documents->firstItem(),
                    'to' => $documents->lastItem(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch documents',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Get active documents for dropdown
     */
    public function getActive()
    {
        try {
            $documents = Document::where('is_active', true)
                ->approved()
                ->orderBy('sort_order')
                ->orderBy('type')
                ->get(['id', 'type', 'color', 'category', 'requires_approval']);

            return response()->json(['data' => $documents]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch documents',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Get document categories for dropdown
     */
    public function getCategories()
    {
        return response()->json(['data' => Document::getCategories()]);
    }

    /**
     * Get single document
     */
    public function show($id)
    {
        try {
            $document = Document::with('approver')->findOrFail($id);
            return response()->json(['data' => $document]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Document not found',
                'errors' => ['id' => ['Document not found']]
            ], 404);
        }
    }

    /**
     * Get pending approvals (for Lawyer dashboard)
     */
    public function getPendingApprovals()
    {
        try {
            $pending = Document::with(['approvalRequests.requester'])
                ->where('requires_approval', true)
                ->where('approval_status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json(['data' => $pending]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch pending approvals',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Get approval history for a document
     */
    public function getApprovalHistory($id)
    {
        try {
            $document = Document::findOrFail($id);
            
            $history = DocumentApproval::with(['requester', 'approver'])
                ->where('document_id', $id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json(['data' => $history]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch approval history',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Helper: Get default color for category
     */
    private function getCategoryColor($category)
    {
        $colors = [
            'Pleading' => '#2563eb',
            'Letter' => '#16a34a',
            'Evidence' => '#b45309',
            'Court Issuance' => '#b91c1c',
            'Other' => '#6b7280',
        ];

        return $colors[$category] ?? '#94a3b8';
    }

    /**
     * Helper: Check if user is lawyer
     */
    private function isLawyer()
    {
        $user = auth()->user();
        $roleName = strtolower($user->role?->name ?? $user->role ?? '');
        return $roleName === 'lawyer';
    }

    /**
     * Create new document
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|max:255|unique:documents,type',
            'category' => 'required|in:' . implode(',', Document::getCategories()),
            'color' => 'nullable|string|max:7|regex:/^#[a-fA-F0-9]{6}$/',
            'requires_approval' => 'nullable|boolean',
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

            $user = auth()->user();
            $isLawyer = $this->isLawyer();

            // Determine approval status
            $requiresApproval = $request->requires_approval ?? false;
            
            if ($isLawyer) {
                // Lawyers are auto-approved for any document they create
                $approvalStatus = 'approved';
                $approvedBy = $user->id;
                $approvedAt = now();
                $message = 'Document created successfully (auto-approved)';
            } else {
                // Non-lawyers: pending if requires_approval is true
                $approvalStatus = $requiresApproval ? 'pending' : 'approved';
                $approvedBy = null;
                $approvedAt = null;
                $message = $requiresApproval 
                    ? 'Document created and pending lawyer approval'
                    : 'Document created successfully';
            }

            // Handle sort order
            $isOthers = strtolower($request->type) === 'others';
            
            if ($isOthers) {
                $sortOrder = 9999;
                // Remove any existing "Others"
                Document::where('type', 'LIKE', '%others%')
                    ->orWhere('type', 'LIKE', '%Others%')
                    ->delete();
            } else {
                // For normal documents, get next available number
                $maxSortOrder = Document::where('sort_order', '<', 9000)->max('sort_order');
                $sortOrder = $maxSortOrder ? $maxSortOrder + 1 : 1;
            }

            $document = Document::create([
                'type' => $request->type,
                'category' => $request->category,
                'color' => $request->color ?? $this->getCategoryColor($request->category),
                'requires_approval' => $requiresApproval,
                'approval_status' => $approvalStatus,
                'approved_by' => $approvedBy,
                'approved_at' => $approvedAt,
                'sort_order' => $sortOrder,
                'is_active' => $request->is_active ?? true,
            ]);

            // Create approval record for history
            DocumentApproval::create([
                'document_id' => $document->id,
                'requested_by' => $user->id,
                'approved_by' => $approvedBy,
                'status' => $approvalStatus,
                'approved_at' => $approvedAt,
            ]);

            // ADD ACTIVITY LOG
            CaseActivityLog::create([
                'case_id' => null,
                'user_id' => $user->id,
                'action' => 'created_document_type',
                'details' => [
                    'message' => $message,
                    'document_id' => $document->id,
                    'document_type' => $document->type,
                    'category' => $document->category,
                    'color' => $document->color,
                    'requires_approval' => $document->requires_approval,
                    'approval_status' => $document->approval_status,
                    'sort_order' => $document->sort_order,
                    'is_active' => $document->is_active,
                ],
            ]);

            DB::commit();

            return response()->json([
                'message' => $message,
                'data' => $document->load('approver')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create document',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Update document
     */
    public function update(Request $request, $id)
    {
        try {
            $document = Document::findOrFail($id);
            $oldValues = $document->toArray();

            $validator = Validator::make($request->all(), [
                'type' => 'sometimes|required|string|max:255|unique:documents,type,' . $id,
                'category' => 'sometimes|required|in:' . implode(',', Document::getCategories()),
                'color' => 'nullable|string|max:7|regex:/^#[a-fA-F0-9]{6}$/',
                'requires_approval' => 'nullable|boolean',
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

            $user = auth()->user();
            $isLawyer = $this->isLawyer();
            $oldStatus = $document->approval_status;

            // Handle approval status changes
            if ($request->has('requires_approval') && $request->requires_approval != $document->requires_approval) {
                if ($isLawyer) {
                    // Lawyers can change approval requirement and it stays approved
                    $document->approval_status = 'approved';
                    $document->approved_by = $user->id;
                    $document->approved_at = now();
                } else {
                    // Non-lawyers changing to requires_approval = true resets to pending
                    if ($request->requires_approval) {
                        $document->approval_status = 'pending';
                        $document->approved_by = null;
                        $document->approved_at = null;
                    }
                }
            }

            // Handle sort order
            $isOthers = strtolower($request->type ?? $document->type) === 'others';
            
            if ($isOthers) {
                $sortOrder = 9999;
            } else {
                $sortOrder = $request->sort_order ?? $document->sort_order;
            }

            $document->update([
                'type' => $request->type ?? $document->type,
                'category' => $request->category ?? $document->category,
                'color' => $request->color ?? $document->color,
                'requires_approval' => $request->requires_approval ?? $document->requires_approval,
                'approval_status' => $document->approval_status,
                'approved_by' => $document->approved_by,
                'approved_at' => $document->approved_at,
                'sort_order' => $sortOrder,
                'is_active' => $request->is_active ?? $document->is_active,
            ]);

            // Create approval record for history if status changed
            if ($document->approval_status !== $oldStatus) {
                DocumentApproval::create([
                    'document_id' => $document->id,
                    'requested_by' => $user->id,
                    'approved_by' => $document->approved_by,
                    'status' => $document->approval_status,
                    'approved_at' => $document->approved_at,
                ]);
            }

            // ADD ACTIVITY LOG
            $changes = [];
            if ($oldValues['type'] != $document->type) $changes['type'] = ['old' => $oldValues['type'], 'new' => $document->type];
            if ($oldValues['category'] != $document->category) $changes['category'] = ['old' => $oldValues['category'], 'new' => $document->category];
            if ($oldValues['color'] != $document->color) $changes['color'] = ['old' => $oldValues['color'], 'new' => $document->color];
            if ($oldValues['requires_approval'] != $document->requires_approval) $changes['requires_approval'] = ['old' => $oldValues['requires_approval'], 'new' => $document->requires_approval];
            if ($oldValues['approval_status'] != $document->approval_status) $changes['approval_status'] = ['old' => $oldValues['approval_status'], 'new' => $document->approval_status];
            if ($oldValues['sort_order'] != $document->sort_order) $changes['sort_order'] = ['old' => $oldValues['sort_order'], 'new' => $document->sort_order];
            if ($oldValues['is_active'] != $document->is_active) $changes['is_active'] = ['old' => $oldValues['is_active'], 'new' => $document->is_active];

            CaseActivityLog::create([
                'case_id' => null,
                'user_id' => $user->id,
                'action' => 'updated_document_type',
                'details' => [
                    'message' => "Updated document type: {$document->type}",
                    'document_id' => $document->id,
                    'document_type' => $document->type,
                    'changes' => $changes,
                ],
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Document updated successfully',
                'data' => $document->fresh(['approver'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update document',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Approve document (Lawyer only)
     */
    public function approve($id)
    {
        try {
            $document = Document::findOrFail($id);

            // Check if user is lawyer
            if (!$this->isLawyer()) {
                return response()->json([
                    'message' => 'Unauthorized',
                    'errors' => ['role' => ['Only lawyers can approve documents']]
                ], 403);
            }

            // If already approved, just return success
            if ($document->approval_status === 'approved') {
                return response()->json([
                    'message' => 'Document is already approved',
                    'data' => $document->load('approver')
                ]);
            }

            if ($document->approval_status !== 'pending') {
                return response()->json([
                    'message' => 'Document is not pending approval',
                    'errors' => ['document' => ['This document is not pending approval']]
                ], 422);
            }

            DB::beginTransaction();

            $user = auth()->user();

            $document->update([
                'approval_status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            DocumentApproval::create([
                'document_id' => $document->id,
                'requested_by' => $document->approvalRequests()->first()?->requested_by ?? $user->id,
                'approved_by' => $user->id,
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            // ADD ACTIVITY LOG
            CaseActivityLog::create([
                'case_id' => null,
                'user_id' => $user->id,
                'action' => 'approved_document_type',
                'details' => [
                    'message' => "Approved document type: {$document->type}",
                    'document_id' => $document->id,
                    'document_type' => $document->type,
                    'previous_status' => 'pending',
                    'new_status' => 'approved',
                ],
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Document approved successfully',
                'data' => $document->fresh(['approver'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to approve document',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Reject document (Lawyer only)
     */
    public function reject(Request $request, $id)
    {
        try {
            $document = Document::findOrFail($id);

            // Check if user is lawyer
            if (!$this->isLawyer()) {
                return response()->json([
                    'message' => 'Unauthorized',
                    'errors' => ['role' => ['Only lawyers can reject documents']]
                ], 403);
            }

            if ($document->approval_status !== 'pending') {
                return response()->json([
                    'message' => 'Document is not pending approval',
                    'errors' => ['document' => ['This document is not pending approval']]
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'rejection_reason' => 'required|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $user = auth()->user();

            $document->update([
                'approval_status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
            ]);

            DocumentApproval::create([
                'document_id' => $document->id,
                'requested_by' => $document->approvalRequests()->first()?->requested_by ?? $user->id,
                'approved_by' => $user->id,
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
            ]);

            // ADD ACTIVITY LOG
            CaseActivityLog::create([
                'case_id' => null,
                'user_id' => $user->id,
                'action' => 'rejected_document_type',
                'details' => [
                    'message' => "Rejected document type: {$document->type}",
                    'document_id' => $document->id,
                    'document_type' => $document->type,
                    'previous_status' => 'pending',
                    'new_status' => 'rejected',
                    'rejection_reason' => $request->rejection_reason,
                ],
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Document rejected',
                'data' => $document->fresh(['approver'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to reject document',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Bulk approve documents (Lawyer only)
     */
    public function bulkApprove(Request $request)
    {
        try {
            if (!$this->isLawyer()) {
                return response()->json([
                    'message' => 'Unauthorized',
                    'errors' => ['role' => ['Only lawyers can approve documents']]
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'document_ids' => 'required|array|min:1',
                'document_ids.*' => 'exists:documents,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $user = auth()->user();
            $documents = Document::whereIn('id', $request->document_ids)
                ->where('requires_approval', true)
                ->where('approval_status', 'pending')
                ->get();

            foreach ($documents as $document) {
                $document->update([
                    'approval_status' => 'approved',
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                ]);

                DocumentApproval::create([
                    'document_id' => $document->id,
                    'requested_by' => $document->approvalRequests()->first()?->requested_by ?? $user->id,
                    'approved_by' => $user->id,
                    'status' => 'approved',
                    'approved_at' => now(),
                ]);

                // ADD ACTIVITY LOG FOR EACH DOCUMENT
                CaseActivityLog::create([
                    'case_id' => null,
                    'user_id' => $user->id,
                    'action' => 'approved_document_type',
                    'details' => [
                        'message' => "Approved document type: {$document->type} (bulk approval)",
                        'document_id' => $document->id,
                        'document_type' => $document->type,
                        'previous_status' => 'pending',
                        'new_status' => 'approved',
                        'bulk_operation' => true,
                    ],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => count($documents) . ' document(s) approved successfully',
                'data' => [
                    'approved_count' => count($documents),
                    'approved_ids' => $documents->pluck('id')
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to bulk approve documents',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Toggle document active status
     */
    public function toggleActive($id)
    {
        try {
            $document = Document::findOrFail($id);
            $oldStatus = $document->is_active;
            
            $document->update(['is_active' => !$document->is_active]);

            // ADD ACTIVITY LOG
            CaseActivityLog::create([
                'case_id' => null,
                'user_id' => auth()->id(),
                'action' => $document->is_active ? 'activated_document_type' : 'deactivated_document_type',
                'details' => [
                    'message' => ($document->is_active ? 'Activated' : 'Deactivated') . " document type: {$document->type}",
                    'document_id' => $document->id,
                    'document_type' => $document->type,
                    'old_status' => $oldStatus,
                    'new_status' => $document->is_active,
                ],
            ]);

            return response()->json([
                'message' => $document->is_active ? 'Document activated' : 'Document deactivated',
                'data' => $document
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to toggle document status',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Delete document
     */
    public function destroy($id)
    {
        try {
            $document = Document::findOrFail($id);
            $documentData = $document->toArray();

            // Don't allow deleting "Others"
            if (strtolower($document->type) === 'others') {
                return response()->json([
                    'message' => 'Cannot delete "Others" document',
                    'errors' => ['document' => ['The "Others" document cannot be deleted']]
                ], 422);
            }

            DB::beginTransaction();

            // Reorder remaining documents
            Document::where('sort_order', '>', $document->sort_order)
                ->where('sort_order', '<', 9000)
                ->decrement('sort_order');

            // ADD ACTIVITY LOG (BEFORE DELETING APPROVAL HISTORY)
            CaseActivityLog::create([
                'case_id' => null,
                'user_id' => auth()->id(),
                'action' => 'deleted_document_type',
                'details' => [
                    'message' => "Deleted document type: {$document->type}",
                    'document_id' => $document->id,
                    'document_type' => $document->type,
                    'category' => $document->category,
                    'color' => $document->color,
                    'requires_approval' => $document->requires_approval,
                    'approval_status' => $document->approval_status,
                    'sort_order' => $document->sort_order,
                    'was_active' => $document->is_active,
                ],
            ]);

            // Delete approval history
            DocumentApproval::where('document_id', $id)->delete();

            $document->delete();

            DB::commit();

            return response()->json([
                'message' => 'Document deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete document',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }
}