<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CaseChecklist;
use App\Models\Cases;
use App\Models\User;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Notification; 


class CaseChecklistController extends Controller
{
    /**
     * Get all checklist items for a case
     */
public function index($caseId)
{
    try {
        $case = Cases::findOrFail($caseId);
        
$items = CaseChecklist::where('case_id', $caseId)
    ->with('document') // eager load to avoid N+1
    ->orderBy('created_at', 'desc')
    ->get()
    ->map(function($item) {
        return [
            'id' => $item->id,
            'case_id' => $item->case_id,
            'document_type_id' => $item->document_type_id,
            'document_type' => $item->document?->type,
            'document_category' => $item->document?->category,
            'document_color' => $item->document?->color,
            'status' => $item->status,
            'due_date' => $item->due_date?->format('Y-m-d'),
            'assigned_clerk_id' => $item->assigned_clerk_id,
            'assigned_to' => $item->assigned_to,
            'notes' => $item->notes,
            'is_out' => $item->is_out,
            'completed_at' => $item->completed_at,
            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
        ];
    });
        return response()->json(['data' => $items]);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to fetch checklist',
            'errors' => ['server' => [$e->getMessage()]]
        ], 500);
    }
}

    /**
     * Store a new checklist item
     */
/**
 * Store a new checklist item
 */
public function store(Request $request, $caseId)
{
    try {
        $case = Cases::findOrFail($caseId);

        $validator = Validator::make($request->all(), [
            'document_type_id' => 'nullable|exists:documents,id',
            'status' => 'required|in:todo,in-progress,done',
            'due_date' => 'nullable|date',
            'assigned_clerk_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        // Get clerk info if assigned
        $clerkId = $request->assigned_clerk_id ?? null;
        $clerk = $clerkId ? User::find($clerkId) : null;

        // If document_type_id is provided, get document details
        $documentType = $request->document_type;
        $documentCategory = $request->document_category;
        $documentColor = $request->document_color;

        if ($request->document_type_id) {
            $document = Document::find($request->document_type_id);
            if ($document) {
                $documentType = $document->type;
                $documentCategory = $document->category;
                $documentColor = $document->color;
            }
        }

        $item = CaseChecklist::create([
            'case_id' => $caseId,
            'created_by' => auth()->id(),
            'document_type_id' => $request->document_type_id,
            'status' => $request->status,
            'due_date' => $request->due_date,
            'assigned_clerk_id' => $clerk?->id,
            'assigned_to' => $clerk?->full_name,
            'notes' => $request->notes,
        ]);

        // Log activity
        \App\Models\CaseActivityLog::create([
            'case_id' => $caseId,
            'user_id' => auth()->id(),
            'action' => 'added_task',
            'details' => json_encode([
                'task' => $request->task,
                'document_type' => $documentType
            ]),
        ]);

        // ========== 🔔 ADD NOTIFICATION FOR TASK ASSIGNMENT ==========
        if ($item->assigned_clerk_id) {
            \App\Models\Notification::create([
                'user_id' => $item->assigned_clerk_id,
                'notifiable_type' => CaseChecklist::class,
                'notifiable_id' => $item->id,
                'type' => 'task_assigned',
                'title' => 'New Task Assigned',
                'message' => "You have been assigned a new task: {$item->task}",
                'data' => [
                    'case_id' => $caseId,
                    'case_code' => $case->case_code,
                    'due_date' => $item->due_date?->format('Y-m-d'),
                    'document_type' => $item->document_type,
                ],
                'action_url' => "/casemaster"
            ]);
        }
        // ========== 🔔 END NOTIFICATION ==========

        DB::commit();

        return response()->json([
            'message' => 'Task created successfully',
            'data' => [
                'id' => $item->id,
                'case_id' => $item->case_id,
                'document_type_id' => $item->document_type_id,
                'status' => $item->status,
                'due_date' => $item->due_date?->format('Y-m-d'),
                'assigned_clerk_id' => $item->assigned_clerk_id,
                'assigned_to' => $item->assigned_to,
                'notes' => $item->notes,
                'is_out' => $item->is_out,
                'completed_at' => $item->completed_at,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ]
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Failed to create task',
            'errors' => ['server' => [$e->getMessage()]]
        ], 500);
    }
}

    /**
     * Update a checklist item
     */
 /**
 * Update a checklist item
 */
public function update(Request $request, $caseId, $id)
{
    try {
        $item = CaseChecklist::where('case_id', $caseId)
            ->where('id', $id)
            ->with('case')
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'document_type_id' => 'nullable|exists:documents,id',
            'status' => 'sometimes|required|in:todo,in-progress,done',
            'due_date' => 'nullable|date',
            'assigned_clerk_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        // Store old values for comparison
        $oldClerkId = $item->assigned_clerk_id;
        $oldStatus = $item->status;

        // Get clerk info if assigned
        $clerkId = $request->assigned_clerk_id ?? null;
        $clerk = $clerkId ? User::find($clerkId) : null;

        // Handle document type
        $documentType = $request->document_type ?? $item->document_type;
        $documentCategory = $request->document_category ?? $item->document_category;
        $documentColor = $request->document_color ?? $item->document_color;
        $documentTypeId = $request->document_type_id ?? $item->document_type_id;

        if ($request->document_type_id && $request->document_type_id != $item->document_type_id) {
            $document = Document::find($request->document_type_id);
            if ($document) {
                $documentType = $document->type;
                $documentCategory = $document->category;
                $documentColor = $document->color;
                $documentTypeId = $document->id;
            }
        }

        $item->update([
            'document_type_id' => $documentTypeId,
            'status' => $request->status ?? $item->status,
            'due_date' => $request->due_date ?? $item->due_date,
            'assigned_clerk_id' => $clerk?->id ?? $item->assigned_clerk_id,
            'assigned_to' => $clerk?->full_name ?? $item->assigned_to,
            'notes' => $request->notes ?? $item->notes,
        ]);

        // If status changed to done, set completed_at
        if ($request->has('status') && $request->status === 'done' && $item->status !== 'done') {
            $item->update(['completed_at' => now()]);
        } elseif ($request->has('status') && $request->status !== 'done' && $item->status === 'done') {
            $item->update(['completed_at' => null]);
        }

        DB::commit();

        // ========== 🔔 ADD NOTIFICATIONS ==========
        
        // CASE 1: Task reassigned to a different clerk
        if ($oldClerkId != $item->assigned_clerk_id && $item->assigned_clerk_id) {
            \App\Models\Notification::create([
                'user_id' => $item->assigned_clerk_id,
                'notifiable_type' => CaseChecklist::class,
                'notifiable_id' => $item->id,
                'type' => 'task_reassigned',
                'title' => 'Task Reassigned',
                'message' => "A task has been reassigned to you: {$item->task}",
                'data' => [
                    'case_id' => $caseId,
                    'case_code' => $item->case?->case_code,
                    'due_date' => $item->due_date?->format('Y-m-d'),
                ],
                'action_url' => "/casemaster"
            ]);
        }

        // CASE 2: Task status changed (for the assigned clerk)
        if ($oldStatus != $item->status && $item->assigned_clerk_id) {
            $statusMessage = $item->status === 'done' ? 'completed' : 'updated to ' . $item->status;
            
            \App\Models\Notification::create([
                'user_id' => $item->assigned_clerk_id,
                'notifiable_type' => CaseChecklist::class,
                'notifiable_id' => $item->id,
                'type' => 'task_status_changed',
                'title' => 'Task Status Updated',
                'message' => "Task '{$item->task}' has been {$statusMessage}",
                'data' => [
                    'case_id' => $caseId,
                    'case_code' => $item->case?->case_code,
                    'task' => $item->task,
                    'old_status' => $oldStatus,
                    'new_status' => $item->status,
                ],
                'action_url' => "/casemaster"
            ]);
        }
        
        // ========== 🔔 END NOTIFICATIONS ==========

        return response()->json([
            'message' => 'Task updated successfully',
            'data' => [
                'id' => $item->id,
                'case_id' => $item->case_id,
                'task' => $item->task,
                'document_type_id' => $item->document_type_id,
                'status' => $item->status,
                'due_date' => $item->due_date?->format('Y-m-d'),
                'assigned_clerk_id' => $item->assigned_clerk_id,
                'assigned_to' => $item->assigned_to,
                'notes' => $item->notes,
                'is_out' => $item->is_out,
                'completed_at' => $item->completed_at,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Failed to update task',
            'errors' => ['server' => [$e->getMessage()]]
        ], 500);
    }
}

    /**
     * Delete a checklist item
     */
    public function destroy($caseId, $id)
    {
        try {
            $item = CaseChecklist::where('case_id', $caseId)
                ->where('id', $id)
                ->firstOrFail();

            DB::beginTransaction();

            $item->delete();

            // Log activity
            \App\Models\CaseActivityLog::create([
                'case_id' => $caseId,
                'user_id' => auth()->id(),
                'action' => 'deleted_task',
                'details' => json_encode(['task' => $item->task]),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Task deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete task',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Update task status only
     */
    public function updateStatus(Request $request, $caseId, $id)
    {
        try {
            $item = CaseChecklist::where('case_id', $caseId)
                ->where('id', $id)
                ->firstOrFail();

            $validator = Validator::make($request->all(), [
                'status' => 'required|in:todo,in-progress,done',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $item->update(['status' => $request->status]);

            if ($request->status === 'done') {
                $item->update(['completed_at' => now()]);
            } elseif ($item->status === 'done') {
                $item->update(['completed_at' => null]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Status updated successfully',
                'data' => [
                    'id' => $item->id,
                    'status' => $item->status,
                    'completed_at' => $item->completed_at,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update status',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }
}