<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cases;
use App\Models\CaseChecklist;
use App\Models\ChecklistMovement;
use App\Models\CaseActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;
use App\Models\User;

class ChecklistTrackerController extends Controller
{
    /**
     * Get all checklist movements for a case
     */
    public function index($caseId)
    {
        try {
            $case = Cases::findOrFail($caseId);
            
            $movements = ChecklistMovement::where('case_id', $caseId)
                ->with([
                    'checklist:id,task',
                    'recorder:id,full_name',
                    'approver:id,full_name'
                ])
                ->orderBy('date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($item) {
                    return [
                        'id' => $item->id,
                        'case_id' => $item->case_id,
                        'checklist_id' => $item->checklist_id,
                        'type' => $item->type,
                        'from_to' => $item->from_to,
                        'date' => $item->date,
                        'purpose' => $item->purpose,
                        'handled_by' => $item->handled_by,
                        'task_name' => $item->task_name ?? $item->checklist?->task,
                        'approval_status' => $item->approval_status,
                        'recorder' => $item->recorder?->full_name,
                        'approver' => $item->approver?->full_name,
                        'approved_at' => $item->approved_at,
                        'created_at' => $item->created_at,
                    ];
                });

            return response()->json(['data' => $movements]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch checklist movements',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Get pending checklist movements for a case
     */
    public function pending($caseId)
    {
        try {
            $movements = ChecklistMovement::where('case_id', $caseId)
                ->where('approval_status', 'PENDING')
                ->with(['checklist:id,task', 'recorder:id,full_name'])
                ->orderBy('date', 'asc')
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json(['data' => $movements]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch pending checklist movements',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Store a new checklist movement
     */
/**
 * Store a new checklist movement
 */
public function store(Request $request, $caseId)
{
    try {
        $case = Cases::findOrFail($caseId);

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:IN,OUT,PENDING',
            'checklist_id' => 'nullable|exists:case_checklists,id',
            'from_to' => 'nullable|string|max:255',
            'date' => 'required|date',
            'purpose' => 'nullable|string|max:500',
            'handled_by' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        $user = auth()->user();
        $roleName = strtolower($user->role?->name ?? $user->role ?? '');
        $isPrivileged = in_array($roleName, ['admin', 'lawyer']);

        $taskName = null;
        $task = null;
        
        if ($request->checklist_id) {
            $task = CaseChecklist::find($request->checklist_id);
            $taskName = $task?->task;
        }

        $movement = ChecklistMovement::create([
            'case_id' => $caseId,
            'checklist_id' => $request->checklist_id,
            'recorded_by' => $user->id,
            'type' => $request->type,
            'from_to' => $request->from_to,
            'date' => $request->date,
            'purpose' => $request->purpose,
            'handled_by' => $request->handled_by,
            'task_name' => $taskName,
            'approval_status' => $isPrivileged ? 'APPROVED' : 'PENDING',
            'approved_by' => $isPrivileged ? $user->id : null,
            'approved_at' => $isPrivileged ? now() : null,
        ]);

        // Update checklist is_out status if approved and specific item
        if ($isPrivileged && $request->checklist_id) {
            CaseChecklist::where('id', $request->checklist_id)
                ->update(['is_out' => $request->type === 'OUT']);
        }

        // SIMPLIFIED AUDIT MESSAGE
        $taskDisplay = $taskName ? $taskName : 'Checklist item';
        $location = $movement->from_to ?: 'unspecified';
        
        $message = $movement->type === 'OUT' 
            ? "Released {$taskDisplay} to {$location}" 
            : "Received {$taskDisplay} from {$location}";
        
        CaseActivityLog::create([
            'case_id' => $caseId,
            'user_id' => $user->id,
            'action' => $movement->type === 'OUT' ? 'checklist_released' : 'checklist_received',
            'details' => [
                'message' => $message,
                'type' => $movement->type,
                'task_name' => $taskName,
                'from_to' => $movement->from_to,
                'handled_by' => $movement->handled_by,
            ],
        ]);

        // ========== 🔔 ADD NOTIFICATIONS HERE ==========
        
        // CASE 1: If movement is pending approval, notify admin and lawyers
        if ($movement->approval_status === 'PENDING') {
            // Get all admin and lawyer users
            $users = User::whereHas('role', function($q) {
                $q->whereIn('name', ['admin', 'lawyer']);
            })->get();
            
            foreach ($users as $recipient) {
                Notification::create([
                    'user_id' => $recipient->id,
                    'notifiable_type' => ChecklistMovement::class,
                    'notifiable_id' => $movement->id,
                    'type' => 'checklist_movement_pending',
                    'title' => 'Checklist Movement Pending Approval',
                    'message' => "Checklist movement for case {$case->case_code} requires approval",
                    'data' => [
                        'case_code' => $case->case_code,
                        'case_id' => $caseId,
                        'task_name' => $taskName,
                        'type' => $movement->type,
                        'from_to' => $movement->from_to,
                    ],
                    'action_url' => '/approvals'
                ]);
            }
        }
        
        // CASE 2: If movement is auto-approved (by admin/lawyer), notify the clerk who recorded it
        if ($movement->approval_status === 'APPROVED' && $movement->recorded_by) {
            $status = $movement->type === 'OUT' ? 'released' : 'received';
            
            Notification::create([
                'user_id' => $movement->recorded_by,
                'notifiable_type' => ChecklistMovement::class,
                'notifiable_id' => $movement->id,
                'type' => 'checklist_movement_approved',
                'title' => 'Checklist Movement Approved',
                'message' => "Your checklist movement for case {$case->case_code} has been approved",
                'data' => [
                    'case_code' => $case->case_code,
                    'task_name' => $taskName,
                    'type' => $movement->type,
                    'from_to' => $movement->from_to,
                    'status' => 'APPROVED',
                ],
                'action_url' => "/casemaster"
            ]);
        }
        
        // ========== 🔔 END NOTIFICATIONS ==========

        DB::commit();

        return response()->json([
            'message' => $isPrivileged 
                ? 'Checklist movement recorded and approved' 
                : 'Checklist movement recorded, pending approval',
            'data' => $movement->load('checklist')
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Failed to create checklist movement',
            'errors' => ['server' => [$e->getMessage()]]
        ], 500);
    }
}

    /**
     * Approve or reject a checklist movement
     */
   /**
 * Approve or reject a checklist movement
 */
public function approve(Request $request, $caseId, $movementId)
{
    try {
        $movement = ChecklistMovement::where('case_id', $caseId)
            ->where('id', $movementId)
            ->with('case')
            ->firstOrFail();

        if ($movement->approval_status !== 'PENDING') {
            return response()->json([
                'message' => 'Movement has already been reviewed',
                'errors' => ['movement' => ['This movement has already been processed']]
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'approval_status' => 'required|in:APPROVED,REJECTED',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        $user = auth()->user();

        $movement->update([
            'approval_status' => $request->approval_status,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        // Update checklist is_out status if approved and specific item
        if ($request->approval_status === 'APPROVED' && $movement->checklist_id) {
            CaseChecklist::where('id', $movement->checklist_id)
                ->update(['is_out' => $movement->type === 'OUT']);
        }

        // SIMPLIFIED AUDIT MESSAGE
        $taskName = $movement->task_name ?: 'Checklist item';
        $status = strtolower($request->approval_status);
        
        $message = $request->approval_status === 'APPROVED'
            ? "{$taskName} movement approved"
            : "{$taskName} movement rejected";
        
        CaseActivityLog::create([
            'case_id' => $caseId,
            'user_id' => $user->id,
            'action' => $request->approval_status === 'APPROVED' ? 'checklist_approved' : 'checklist_rejected',
            'details' => [
                'message' => $message,
                'task_name' => $movement->task_name,
                'from_to' => $movement->from_to,
                'approval_status' => $request->approval_status,
            ],
        ]);

        // ========== 🔔 ADD NOTIFICATIONS HERE ==========
        
        // Notify the recorder about the decision
        if ($movement->recorded_by) {
            $case = Cases::find($caseId);
            
            Notification::create([
                'user_id' => $movement->recorded_by,
                'notifiable_type' => ChecklistMovement::class,
                'notifiable_id' => $movement->id,
                'type' => 'checklist_movement_' . strtolower($request->approval_status),
                'title' => "Checklist Movement {$request->approval_status}",
                'message' => "Your checklist movement for case {$case->case_code} was {$status}",
                'data' => [
                    'case_code' => $case->case_code,
                    'task_name' => $movement->task_name,
                    'type' => $movement->type,
                    'from_to' => $movement->from_to,
                    'status' => $request->approval_status,
                ],
                'action_url' => $request->approval_status === 'APPROVED' ? "/casemaster" : null
            ]);
        }
        
        // ========== 🔔 END NOTIFICATIONS ==========

        DB::commit();

        return response()->json([
            'message' => 'Movement ' . strtolower($request->approval_status),
            'data' => $movement->fresh()
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Failed to approve movement',
            'errors' => ['server' => [$e->getMessage()]]
        ], 500);
    }
}
}