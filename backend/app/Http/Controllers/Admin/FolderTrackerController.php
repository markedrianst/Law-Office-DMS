<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cases;
use App\Models\FolderMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class FolderTrackerController extends Controller
{
    /**
     * Get all folder movements for a case
     */
    public function index($caseId)
    {
        try {
            $case = Cases::findOrFail($caseId);
            
            $movements = FolderMovement::where('case_id', $caseId)
                ->with(['recorder:id,full_name', 'approver:id,full_name'])
                ->orderBy('date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($item) {
                    return [
                        'id' => $item->id,
                        'case_id' => $item->case_id,
                        'type' => $item->type,
                        'from_to' => $item->from_to,
                        'date' => $item->date,
                        'purpose' => $item->purpose,
                        'handled_by' => $item->handled_by,
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
                'message' => 'Failed to fetch folder movements',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Get pending folder movements for a case
     */
    public function pending($caseId)
    {
        try {
            $movements = FolderMovement::where('case_id', $caseId)
                ->where('approval_status', 'PENDING')
                ->with('recorder:id,full_name')
                ->orderBy('date', 'asc')
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json(['data' => $movements]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch pending folder movements',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Store a new folder movement
     */
    public function store(Request $request, $caseId)
    {
        try {
            $case = Cases::findOrFail($caseId);

            $validator = Validator::make($request->all(), [
                'type' => 'required|in:IN,OUT,PENDING',
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

            $movement = FolderMovement::create([
                'case_id' => $caseId,
                'recorded_by' => $user->id,
                'type' => $request->type,
                'from_to' => $request->from_to,
                'date' => $request->date,
                'purpose' => $request->purpose,
                'handled_by' => $request->handled_by,
                'approval_status' => $isPrivileged ? 'APPROVED' : 'PENDING',
                'approved_by' => $isPrivileged ? $user->id : null,
                'approved_at' => $isPrivileged ? now() : null,
            ]);

            // Update case is_out status if approved
            if ($isPrivileged) {
                $case->update(['is_out' => $request->type === 'OUT']);
            }

            DB::commit();

            return response()->json([
                'message' => $isPrivileged 
                    ? 'Folder movement recorded and approved' 
                    : 'Folder movement recorded, pending approval',
                'data' => $movement
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create folder movement',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Approve or reject a folder movement
     */
    public function approve(Request $request, $caseId, $movementId)
    {
        try {
            $movement = FolderMovement::where('case_id', $caseId)
                ->where('id', $movementId)
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

            // Update case is_out status if approved
            if ($request->approval_status === 'APPROVED') {
                $case = Cases::find($caseId);
                $case->update(['is_out' => $movement->type === 'OUT']);
            }

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