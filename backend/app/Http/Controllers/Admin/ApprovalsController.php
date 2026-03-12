<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChecklistMovement;
use App\Models\FolderMovement;
use App\Models\CaseActivityLog;
use App\Models\Cases;
use App\Models\CaseChecklist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ApprovalsController extends Controller
{
    // No constructor needed - we'll check roles in each method
    
    /**
     * Check if user has admin or lawyer role
     */
    private function checkRole()
    {
        $user = auth()->user();
        
        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        $roleName = strtolower($user->role?->name ?? $user->role ?? '');
        
        if (!in_array($roleName, ['admin', 'lawyer'])) {
            abort(403, 'Unauthorized. Only admin and lawyer can access approvals.');
        }
        
        return true;
    }

    /**
     * GET /admin/approvals
     * Get all movements with filters
     */
    public function index(Request $request): JsonResponse
    {
        // Check role at the beginning of each method
        $this->checkRole();
        
        try {
            $status = $request->input('status', 'ALL');
            $type = $request->input('type', 'all');
            $direction = $request->input('direction', 'ALL');
            $search = $request->input('search', '');

            $result = [];

            // Get checklist movements
            if ($type === 'all' || $type === 'checklist') {
                $query = ChecklistMovement::with([
                        'checklist:id,task',
                        'recorder:id,full_name',
                        'approver:id,full_name',
                        'case:id,case_code'
                    ])
                    ->orderBy('created_at', 'desc');

                if ($status !== 'ALL') {
                    $query->where('approval_status', $status);
                }

                if ($direction !== 'ALL') {
                    $query->where('type', $direction);
                }

                $checklists = $query->get();

                foreach ($checklists as $m) {
                    $result[] = [
                        'id' => $m->id,
                        'source' => 'checklist',
                        'case_id' => $m->case_id,
                        'case_code' => $m->case?->case_code,
                        'type' => $m->type,
                        'approval_status' => $m->approval_status,
                        'from_to' => $m->from_to,
                        'date' => $m->date,
                        'purpose' => $m->purpose,
                        'handled_by' => $m->handled_by,
                        'task_name' => $m->task_name,
                        'notes' => $m->notes,
                        'checklist' => $m->checklist ? [
                            'id' => $m->checklist->id,
                            'task' => $m->checklist->task
                        ] : null,
                        'recorder' => $m->recorder ? [
                            'id' => $m->recorder->id,
                            'full_name' => $m->recorder->full_name
                        ] : null,
                        'approver' => $m->approver ? [
                            'id' => $m->approver->id,
                            'full_name' => $m->approver->full_name
                        ] : null,
                        'created_at' => $m->created_at,
                        'approved_at' => $m->approved_at
                    ];
                }
            }

            // Get folder movements
            if ($type === 'all' || $type === 'folder') {
                $query = FolderMovement::with([
                        'recorder:id,full_name',
                        'approver:id,full_name',
                        'case:id,case_code'
                    ])
                    ->orderBy('created_at', 'desc');

                if ($status !== 'ALL') {
                    $query->where('approval_status', $status);
                }

                if ($direction !== 'ALL') {
                    $query->where('type', $direction);
                }

                $folders = $query->get();

                foreach ($folders as $m) {
                    $result[] = [
                        'id' => $m->id,
                        'source' => 'folder',
                        'case_id' => $m->case_id,
                        'case_code' => $m->case?->case_code,
                        'type' => $m->type,
                        'approval_status' => $m->approval_status,
                        'from_to' => $m->from_to,
                        'date' => $m->date,
                        'purpose' => $m->purpose,
                        'handled_by' => $m->handled_by,
                        'notes' => $m->notes,
                        'recorder' => $m->recorder ? [
                            'id' => $m->recorder->id,
                            'full_name' => $m->recorder->full_name
                        ] : null,
                        'approver' => $m->approver ? [
                            'id' => $m->approver->id,
                            'full_name' => $m->approver->full_name
                        ] : null,
                        'created_at' => $m->created_at,
                        'approved_at' => $m->approved_at
                    ];
                }
            }

            // Apply search filter
            if ($search) {
                $search = strtolower($search);
                $result = array_filter($result, function($item) use ($search) {
                    return str_contains(strtolower($item['case_code'] ?? ''), $search)
                        || str_contains(strtolower($item['from_to'] ?? ''), $search)
                        || str_contains(strtolower($item['handled_by'] ?? ''), $search)
                        || str_contains(strtolower($item['purpose'] ?? ''), $search)
                        || str_contains(strtolower($item['task_name'] ?? ''), $search)
                        || str_contains(strtolower($item['recorder']['full_name'] ?? ''), $search);
                });
            }

            // Sort: PENDING first, then by date
            usort($result, function($a, $b) {
                if ($a['approval_status'] === 'PENDING' && $b['approval_status'] !== 'PENDING') return -1;
                if ($a['approval_status'] !== 'PENDING' && $b['approval_status'] === 'PENDING') return 1;
                return strtotime($b['date']) - strtotime($a['date']);
            });

            // Calculate stats
            $stats = [
                'total' => count($result),
                'pending' => count(array_filter($result, fn($i) => $i['approval_status'] === 'PENDING')),
                'approved' => count(array_filter($result, fn($i) => $i['approval_status'] === 'APPROVED')),
                'rejected' => count(array_filter($result, fn($i) => $i['approval_status'] === 'REJECTED'))
            ];

            return response()->json([
                'success' => true,
                'data' => array_values($result),
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch approvals',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /admin/approvals/pending-count
     * Get count of pending approvals for badge
     */
    public function pendingCount(): JsonResponse
    {
        // Check role
        $this->checkRole();
        
        try {
            $count = ChecklistMovement::where('approval_status', 'PENDING')->count()
                   + FolderMovement::where('approval_status', 'PENDING')->count();

            return response()->json([
                'success' => true,
                'count' => $count
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'count' => 0
            ], 500);
        }
    }

    /**
     * PATCH /admin/approvals/{type}/{id}/approve
     * Approve or reject a movement with CASE ACTIVITY LOGGING
     */
/**
 * PATCH /admin/approvals/{type}/{id}/approve
 * Approve or reject a movement with CASE ACTIVITY LOGGING
 */
public function approve(Request $request, string $type, int $id): JsonResponse
{
    // Check role
    $this->checkRole();
    
    // Validate request
    $validator = Validator::make($request->all(), [
        'status' => 'required|in:APPROVED,REJECTED',
        'notes' => 'nullable|string|max:500'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        DB::beginTransaction();

        $user = auth()->user();
        $status = $request->status;
        $notes = $request->notes;

        // Process based on type
        if ($type === 'checklist') {
            // Find the checklist movement
            $movement = ChecklistMovement::with(['case', 'checklist', 'recorder'])->findOrFail($id);
            
            // Check if already reviewed
            if ($movement->approval_status !== 'PENDING') {
                return response()->json([
                    'success' => false,
                    'message' => 'This movement has already been reviewed'
                ], 422);
            }

            // Update the movement
            $movement->update([
                'approval_status' => $status,
                'approved_by' => $user->id,
                'approved_at' => now(),
                'notes' => $notes
            ]);

            // 🔥 FIX: Update checklist is_out status based on movement type
            if ($status === 'APPROVED' && $movement->checklist_id) {
                // If approved, set is_out based on movement type
                // OUT = true, IN = false
                CaseChecklist::where('id', $movement->checklist_id)
                    ->update(['is_out' => $movement->type === 'OUT']);
                
                // Also update the movement's task_name if not set
                if (!$movement->task_name) {
                    $checklist = CaseChecklist::find($movement->checklist_id);
                    $movement->update(['task_name' => $checklist?->task]);
                }
            }

            // CREATE CASE ACTIVITY LOG
            $actionMessage = $status === 'APPROVED' 
                ? "Approved {$movement->type} checklist movement" 
                : "Rejected checklist movement";
                
            if ($movement->task_name) {
                $actionMessage .= " for '{$movement->task_name}'";
            }
            if ($movement->from_to) {
                $actionMessage .= $movement->type === 'OUT' ? " to {$movement->from_to}" : " from {$movement->from_to}";
            }
            
            CaseActivityLog::create([
                'case_id' => $movement->case_id,
                'user_id' => $user->id,
                'action' => $status === 'APPROVED' ? 'approved_checklist_movement' : 'rejected_checklist_movement',
                'details' => [
                    'message' => $actionMessage,
                    'movement_id' => $movement->id,
                    'checklist_id' => $movement->checklist_id,
                    'task_name' => $movement->task_name ?? $movement->checklist?->task,
                    'type' => $movement->type,
                    'from_to' => $movement->from_to,
                    'date' => $movement->date,
                    'status' => $status,
                    'notes' => $notes,
                    'recorded_by' => $movement->recorder?->full_name,
                    'recorded_by_id' => $movement->recorded_by,
                    'case_code' => $movement->case?->case_code
                ]
            ]);

        } else if ($type === 'folder') {
            // Find the folder movement
            $movement = FolderMovement::with(['case', 'recorder'])->findOrFail($id);
            
            // Check if already reviewed
            if ($movement->approval_status !== 'PENDING') {
                return response()->json([
                    'success' => false,
                    'message' => 'This movement has already been reviewed'
                ], 422);
            }

            // Update the movement
            $movement->update([
                'approval_status' => $status,
                'approved_by' => $user->id,
                'approved_at' => now(),
                'notes' => $notes
            ]);

            // 🔥 FIX: Update case is_out status based on movement type
            if ($status === 'APPROVED') {
                Cases::where('id', $movement->case_id)
                    ->update(['is_out' => $movement->type === 'OUT']);
            }

            // CREATE CASE ACTIVITY LOG
            $actionMessage = $status === 'APPROVED' 
                ? "Approved {$movement->type} folder movement" 
                : "Rejected folder movement";
                
            if ($movement->from_to) {
                $actionMessage .= $movement->type === 'OUT' ? " to {$movement->from_to}" : " from {$movement->from_to}";
            }
            
            CaseActivityLog::create([
                'case_id' => $movement->case_id,
                'user_id' => $user->id,
                'action' => $status === 'APPROVED' ? 'approved_folder_movement' : 'rejected_folder_movement',
                'details' => [
                    'message' => $actionMessage,
                    'movement_id' => $movement->id,
                    'type' => $movement->type,
                    'from_to' => $movement->from_to,
                    'date' => $movement->date,
                    'purpose' => $movement->purpose,
                    'handled_by' => $movement->handled_by,
                    'status' => $status,
                    'notes' => $notes,
                    'recorded_by' => $movement->recorder?->full_name,
                    'recorded_by_id' => $movement->recorded_by,
                    'case_code' => $movement->case?->case_code
                ]
            ]);

        } else {
            return response()->json([
                'success' => false,
                'message' => 'Invalid type. Must be "checklist" or "folder"'
            ], 422);
        }

        DB::commit();

        // Load relationships for response
        $movement->load(['approver:id,full_name']);

        return response()->json([
            'success' => true,
            'message' => 'Movement ' . strtolower($status) . ' successfully',
            'data' => $movement
        ]);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => ucfirst($type) . ' movement not found'
        ], 404);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Failed to process approval',
            'error' => $e->getMessage()
        ], 500);
    }
}
    
    public function caseHistory(int $caseId): JsonResponse
    {
        
        $this->checkRole();
        
        try {
            // Get folder movements for this case
            $folderMovements = FolderMovement::with(['recorder', 'approver'])
                ->where('case_id', $caseId)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($m) {
                    return [
                        'id' => $m->id,
                        'source' => 'folder',
                        'type' => $m->type,
                        'approval_status' => $m->approval_status,
                        'from_to' => $m->from_to,
                        'date' => $m->date,
                        'purpose' => $m->purpose,
                        'handled_by' => $m->handled_by,
                        'notes' => $m->notes,
                        'recorded_by' => $m->recorder?->full_name,
                        'approved_by' => $m->approver?->full_name,
                        'approved_at' => $m->approved_at,
                        'created_at' => $m->created_at
                    ];
                });

            $checklistMovements = ChecklistMovement::with(['recorder', 'approver', 'checklist'])
                ->where('case_id', $caseId)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($m) {
                    return [
                        'id' => $m->id,
                        'source' => 'checklist',
                        'type' => $m->type,
                        'approval_status' => $m->approval_status,
                        'from_to' => $m->from_to,
                        'date' => $m->date,
                        'purpose' => $m->purpose,
                        'handled_by' => $m->handled_by,
                        'task_name' => $m->task_name ?? $m->checklist?->task,
                        'notes' => $m->notes,
                        'recorded_by' => $m->recorder?->full_name,
                        'approved_by' => $m->approver?->full_name,
                        'approved_at' => $m->approved_at,
                        'created_at' => $m->created_at
                    ];
                });

            $history = collect([...$folderMovements, ...$checklistMovements])
                ->sortByDesc('created_at')
                ->values();

            return response()->json([
                'success' => true,
                'data' => $history
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch case history',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}