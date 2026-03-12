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
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($item) {
                    return [
                        'id' => $item->id,
                        'case_id' => $item->case_id,
                        'task' => $item->task,
                        'document_type_id' => $item->document_type_id,
                        'document_type' => $item->document_type,
                        'document_category' => $item->document_category,
                        'document_color' => $item->document_color,
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
    public function store(Request $request, $caseId)
    {
        try {
            $case = Cases::findOrFail($caseId);

            $validator = Validator::make($request->all(), [
                'task' => 'required|string|max:500',
                'document_type_id' => 'nullable|exists:documents,id',
                'document_type' => 'nullable|string|max:255',
                'document_category' => 'nullable|string|max:100',
                'document_color' => 'nullable|string|max:7',
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
                'task' => $request->task,
                'document_type_id' => $request->document_type_id,
                'document_type' => $documentType,
                'document_category' => $documentCategory,
                'document_color' => $documentColor,
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

            DB::commit();

            return response()->json([
                'message' => 'Task created successfully',
                'data' => [
                    'id' => $item->id,
                    'case_id' => $item->case_id,
                    'task' => $item->task,
                    'document_type_id' => $item->document_type_id,
                    'document_type' => $item->document_type,
                    'document_category' => $item->document_category,
                    'document_color' => $item->document_color,
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
    public function update(Request $request, $caseId, $id)
    {
        try {
            $item = CaseChecklist::where('case_id', $caseId)
                ->where('id', $id)
                ->firstOrFail();

            $validator = Validator::make($request->all(), [
                'task' => 'sometimes|required|string|max:500',
                'document_type_id' => 'nullable|exists:documents,id',
                'document_type' => 'nullable|string|max:255',
                'document_category' => 'nullable|string|max:100',
                'document_color' => 'nullable|string|max:7',
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
                'task' => $request->task ?? $item->task,
                'document_type_id' => $documentTypeId,
                'document_type' => $documentType,
                'document_category' => $documentCategory,
                'document_color' => $documentColor,
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

            return response()->json([
                'message' => 'Task updated successfully',
                'data' => [
                    'id' => $item->id,
                    'case_id' => $item->case_id,
                    'task' => $item->task,
                    'document_type_id' => $item->document_type_id,
                    'document_type' => $item->document_type,
                    'document_category' => $item->document_category,
                    'document_color' => $item->document_color,
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