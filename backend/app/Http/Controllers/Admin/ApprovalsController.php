<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChecklistMovement;
use App\Models\FolderMovement;
use App\Models\CaseActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ApprovalsController extends Controller
{
    /**
     * GET /admin/approvals
     * Smart polling with timestamp check
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $status    = strtoupper($request->input('status',    'ALL'));
            $type      = strtolower($request->input('type',      'all'));
            $direction = strtoupper($request->input('direction', 'ALL'));
            $search    = $request->input('search', '');
            $lastPoll  = $request->input('last_poll');
            
            // Get last modified timestamp
            $lastModified = Cache::get('approvals:last_modified', 0);
            
            // If no changes since last poll
            if ($lastPoll && $lastModified <= $lastPoll) {
                return response()->json(['changed' => false]);
            }
            
            // Fetch fresh data
            $data = $this->fetchApprovals($status, $type, $direction, $search);
            
            return response()->json([
                'data' => $data,
                'changed' => true,
                'timestamp' => now()->timestamp
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch approvals',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /admin/approvals/pending-count
     * Lightweight endpoint for badge
     */
    public function pendingCount(Request $request): JsonResponse
    {
        try {
            $lastPoll = $request->input('last_poll');
            $lastCount = $request->input('last_count');
            
            $count = Cache::remember('approvals:pending_count', 10, function() {
                return ChecklistMovement::where('approval_status', 'PENDING')->count()
                     + FolderMovement::where('approval_status', 'PENDING')->count();
            });
            
            $lastModified = Cache::get('approvals:pending_count_modified', 0);
            
            if ($lastPoll && $lastModified <= $lastPoll && $lastCount == $count) {
                return response()->json(['changed' => false]);
            }
            
            return response()->json([
                'count' => $count,
                'changed' => true,
                'timestamp' => now()->timestamp
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['count' => 0, 'changed' => false]);
        }
    }

    /**
     * PATCH /admin/approvals/{type}/{id}/approve
     */
    public function approve(Request $request, string $type, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:APPROVED,REJECTED',
                'notes' => 'nullable|string|max:500'
            ]);

            DB::beginTransaction();

            if ($type === 'checklist') {
                $movement = ChecklistMovement::with('case')->findOrFail($id);
                
                if ($movement->approval_status !== 'PENDING') {
                    return response()->json([
                        'message' => 'Already reviewed'
                    ], 422);
                }

                $movement->update([
                    'approval_status' => $validated['status'],
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'notes' => $validated['notes'] ?? null
                ]);

                // Update checklist status if approved
                if ($validated['status'] === 'APPROVED' && $movement->checklist_id) {
                    \App\Models\CaseChecklist::where('id', $movement->checklist_id)
                        ->update(['is_out' => $movement->type === 'OUT']);
                }

            } else if ($type === 'folder') {
                $movement = FolderMovement::with('case')->findOrFail($id);
                
                if ($movement->approval_status !== 'PENDING') {
                    return response()->json([
                        'message' => 'Already reviewed'
                    ], 422);
                }

                $movement->update([
                    'approval_status' => $validated['status'],
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'notes' => $validated['notes'] ?? null
                ]);

                // Update case status if approved
                if ($validated['status'] === 'APPROVED') {
                    \App\Models\Cases::where('id', $movement->case_id)
                        ->update(['is_out' => $movement->type === 'OUT']);
                }
            } else {
                return response()->json(['message' => 'Invalid type'], 422);
            }

            // Log activity
            CaseActivityLog::create([
                'case_id' => $movement->case_id,
                'user_id' => auth()->id(),
                'action' => $validated['status'] === 'APPROVED' ? 'approved_movement' : 'rejected_movement',
                'details' => [
                    'type' => $type,
                    'movement_id' => $movement->id,
                    'notes' => $validated['notes'] ?? null
                ]
            ]);

            // Update cache timestamps
            Cache::put('approvals:last_modified', now()->timestamp);
            Cache::put('approvals:pending_count_modified', now()->timestamp);
            Cache::forget('approvals:pending_count');

            DB::commit();

            return response()->json([
                'message' => 'Movement ' . strtolower($validated['status']) . ' successfully',
                'data' => $movement->fresh()->load(['recorder', 'approver', 'case'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to process approval',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch approvals from database
     */
    private function fetchApprovals($status, $type, $direction, $search)
    {
        $result = [];

        // Fetch checklist movements
        if ($type === 'all' || $type === 'checklist') {
            $query = ChecklistMovement::with([
                'checklist:id,task',
                'recorder:id,full_name',
                'approver:id,full_name',
                'case:id,case_code'
            ]);

            if ($status !== 'ALL') {
                $query->where('approval_status', $status);
            }
            if ($direction !== 'ALL') {
                $query->where('type', $direction);
            }

            $checklists = $query->latest()->get();

            foreach ($checklists as $m) {
                $item = $m->toArray();
                $item['_source'] = 'checklist';
                $item['case_code'] = $m->case?->case_code;
                $result[] = $item;
            }
        }

        // Fetch folder movements
        if ($type === 'all' || $type === 'folder') {
            $query = FolderMovement::with([
                'recorder:id,full_name',
                'approver:id,full_name',
                'case:id,case_code'
            ]);

            if ($status !== 'ALL') {
                $query->where('approval_status', $status);
            }
            if ($direction !== 'ALL') {
                $query->where('type', $direction);
            }

            $folders = $query->latest()->get();

            foreach ($folders as $m) {
                $item = $m->toArray();
                $item['_source'] = 'folder';
                $item['case_code'] = $m->case?->case_code;
                $result[] = $item;
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
                    || str_contains(strtolower($item['recorder']['full_name'] ?? ''), $search);
            });
        }

        // Sort: PENDING first, then by date
        usort($result, function($a, $b) {
            if ($a['approval_status'] === 'PENDING' && $b['approval_status'] !== 'PENDING') return -1;
            if ($a['approval_status'] !== 'PENDING' && $b['approval_status'] === 'PENDING') return 1;
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return array_values($result);
    }
}