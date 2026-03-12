<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cases;
use App\Models\CaseActivityLog;
use App\Models\CaseCategory;
use App\Models\CaseStage;
use App\Models\CaseStageHistory;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CaseController extends Controller
{
    /**
     * Display a listing of cases with filters and pagination.
     */
    public function index(Request $request)
    {
        try {
            $query = Cases::with([
                'category:id,name,color',
                'client:id,full_name',
                'lawyer:id,full_name',
                'clerk:id,full_name',
                'currentStage:id,name,color'
            ]);

            // Filter by status
            if ($request->filled('case_status')) {
                $query->where('case_status', $request->case_status);
            }

            // Filter by priority
            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }

            // Filter by stage
            if ($request->filled('stage_id')) {
                $query->where('current_stage_id', $request->stage_id);
            }

            // Filter by assigned lawyer
            if ($request->filled('assigned_lawyer_id')) {
                $query->where('assigned_lawyer_id', $request->assigned_lawyer_id);
            }

            // Filter by assigned clerk
            if ($request->filled('assigned_clerk_id')) {
                $query->where('assigned_clerk_id', $request->assigned_clerk_id);
            }

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('case_code', 'like', "%{$search}%")
                      ->orWhere('case_no', 'like', "%{$search}%")
                      ->orWhere('title', 'like', "%{$search}%")
                      ->orWhereHas('client', function($clientQuery) use ($search) {
                          $clientQuery->where('full_name', 'like', "%{$search}%");
                      });
                });
            }

            // Sorting
            $sortField = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            
            $allowedSorts = ['case_code', 'case_no', 'title', 'priority', 'case_status', 'created_at'];
            $sortField = in_array($sortField, $allowedSorts) ? $sortField : 'created_at';
            
            $query->orderBy($sortField, $sortDirection);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $cases = $query->paginate($perPage);

            // Transform data
            $transformed = $cases->map(function($case) {
                return [
                    'id' => $case->id,
                    'case_code' => $case->case_code,
                    'case_no' => $case->case_no,
                    'title' => $case->title,
                    'category_id' => $case->category_id,
                    'category' => $case->category?->name ?? '—',
                    'category_color' => $case->category?->color ?? '#1a4972',
                    'client_id' => $case->client_id,
                    'client' => $case->client?->full_name ?? '—',
                    'court_or_office' => $case->court_or_office,
                    'docket_no' => $case->docket_no,
                    'assigned_lawyer_id' => $case->assigned_lawyer_id,
                    'lawyer' => $case->lawyer?->full_name ?? '—',
                    'assigned_clerk_id' => $case->assigned_clerk_id,
                    'clerk' => $case->clerk?->full_name ?? '—',
                    'priority' => $case->priority,
                    'case_status' => $case->case_status,
                    'current_stage_id' => $case->current_stage_id,
                    'stage' => $case->currentStage?->name ?? '—',
                    'stage_color' => $case->currentStage?->color ?? '#64748b',
                    'summary' => $case->summary,
                    'is_out' => $case->is_out,
                    'created_at' => $case->created_at,
                    'updated_at' => $case->updated_at,
                ];
            });

            return response()->json([
                'data' => $transformed,
                'meta' => [
                    'current_page' => $cases->currentPage(),
                    'last_page' => $cases->lastPage(),
                    'per_page' => $cases->perPage(),
                    'total' => $cases->total(),
                    'from' => $cases->firstItem(),
                    'to' => $cases->lastItem(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch cases',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Get lookup data for case form (categories, stages, users, clients)
     */
    public function getLookups()
    {
        try {
            $lawyers = User::whereHas('role', function($q) {
                    $q->where('name', 'lawyer');
                })
                ->where('status', 'active')
                ->get(['id', 'full_name']);

            $clerks = User::whereHas('role', function($q) {
                    $q->where('name', 'clerk');
                })
                ->where('status', 'active')
                ->get(['id', 'full_name']);

            $data = [
                'categories' => CaseCategory::where('is_active', true)
                    ->orderBy('sort_order')
                    ->get(['id', 'name', 'color']),
                'stages' => CaseStage::where('is_active', true)
                    ->orderBy('order')
                    ->get(['id', 'name', 'color']),
                'lawyers' => $lawyers,
                'clerks' => $clerks,
                'clients' => Client::orderBy('full_name')
                    ->get(['id', 'full_name', 'contact_no', 'email']),
                'courts' => \App\Models\Court::where('is_active', true)
                    ->orderBy('name')
                    ->get(['id', 'name', 'type', 'address']),
                // Add users array combining lawyers and clerks for From/To dropdown
                'users' => collect($lawyers)->map(function($lawyer) {
                        return [
                            'id' => $lawyer->id,
                            'full_name' => $lawyer->full_name,
                            'role' => 'lawyer'
                        ];
                    })->concat(
                        collect($clerks)->map(function($clerk) {
                            return [
                                'id' => $clerk->id,
                                'full_name' => $clerk->full_name,
                                'role' => 'clerk'
                            ];
                        })
                    )->values()->all(),
            ];

            return response()->json(['data' => $data]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch lookups',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Store a newly created case.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'case_no' => 'required|string|max:180|unique:cases,case_no',
            'title' => 'required|string|max:200',
            'category_id' => 'nullable|exists:case_categories,id',
            'client_id' => 'required|exists:clients,id',
            'court_or_office' => 'nullable|string|max:180',
            'docket_no' => 'nullable|string|max:80',
            'assigned_lawyer_id' => 'required|exists:users,id',
            'assigned_clerk_id' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,normal,urgent',
            'case_status' => 'required|in:active,closed,archived',
            'current_stage_id' => 'nullable|exists:case_stages,id',
            'summary' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Generate case code
            $year = date('Y');
            $lastCase = Cases::whereYear('created_at', $year)
                ->orderBy('id', 'desc')
                ->first();
            
            $sequence = $lastCase ? intval(substr($lastCase->case_code, -4)) + 1 : 1;
            $caseCode = $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            $case = Cases::create([
                'case_no' => $request->case_no,
                'case_code' => $caseCode,
                'title' => $request->title,
                'category_id' => $request->category_id,
                'client_id' => $request->client_id,
                'court_or_office' => $request->court_or_office,
                'docket_no' => $request->docket_no,
                'assigned_lawyer_id' => $request->assigned_lawyer_id,
                'assigned_clerk_id' => $request->assigned_clerk_id,
                'priority' => $request->priority,
                'case_status' => $request->case_status,
                'current_stage_id' => $request->current_stage_id,
                'summary' => $request->summary,
                'created_by' => auth()->id(),
            ]);

            // Log activity
            CaseActivityLog::create([
                'case_id' => $case->id,
                'user_id' => auth()->id(),
                'action' => 'created_case',
                'details' => json_encode([
                    'case_no' => $case->case_no,
                    'title' => $case->title
                ]),
            ]);

            // Create stage history if stage is set
            if ($request->current_stage_id) {
                CaseStageHistory::create([
                    'case_id' => $case->id,
                    'from_stage_id' => null,
                    'to_stage_id' => $request->current_stage_id,
                    'changed_by' => auth()->id(),
                    'remarks' => 'Initial stage',
                ]);
            }

            DB::commit();

            // Load relationships for response
            $case->load([
                'category:id,name,color',
                'client:id,full_name',
                'lawyer:id,full_name',
                'clerk:id,full_name',
                'currentStage:id,name,color'
            ]);

            return response()->json([
                'message' => 'Case created successfully',
                'data' => [
                    'id' => $case->id,
                    'case_code' => $case->case_code,
                    'case_no' => $case->case_no,
                    'title' => $case->title,
                    'category_id' => $case->category_id,
                    'category' => $case->category?->name ?? '—',
                    'category_color' => $case->category?->color ?? '#1a4972',
                    'client_id' => $case->client_id,
                    'client' => $case->client?->full_name ?? '—',
                    'court_or_office' => $case->court_or_office,
                    'docket_no' => $case->docket_no,
                    'assigned_lawyer_id' => $case->assigned_lawyer_id,
                    'lawyer' => $case->lawyer?->full_name ?? '—',
                    'assigned_clerk_id' => $case->assigned_clerk_id,
                    'clerk' => $case->clerk?->full_name ?? '—',
                    'priority' => $case->priority,
                    'case_status' => $case->case_status,
                    'current_stage_id' => $case->current_stage_id,
                    'stage' => $case->currentStage?->name ?? '—',
                    'stage_color' => $case->currentStage?->color ?? '#64748b',
                    'summary' => $case->summary,
                    'is_out' => $case->is_out,
                    'created_at' => $case->created_at,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create case',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Display the specified case with all related data.
     */
    public function show($id)
    {
        try {
            $case = Cases::with([
                'category:id,name,color',
                'client:id,full_name,contact_no,email,address',
                'lawyer:id,full_name',
                'clerk:id,full_name',
                'currentStage:id,name,color',
                'creator:id,full_name',
                
                // Load all checklists
                'checklists' => function($q) {
                    $q->orderBy('created_at');
                },
                
                // Load folder movements with approval info
                'folderMovements' => function($q) {
                    $q->with(['recorder:id,full_name', 'approver:id,full_name'])
                      ->orderBy('date', 'desc')
                      ->orderBy('created_at', 'desc');
                },
                
                // Load checklist movements with all relations
                'checklistMovements' => function($q) {
                    $q->with([
                        'checklist:id,task',
                        'recorder:id,full_name',
                        'approver:id,full_name'
                    ])->orderBy('date', 'desc')
                      ->orderBy('created_at', 'desc');
                },
                
                // Load stage history
                'stageHistories' => function($q) {
                    $q->with(['fromStage:id,name', 'toStage:id,name', 'changedBy:id,full_name'])
                      ->orderBy('created_at', 'desc');
                },
                
                // Load activity logs
                'activityLogs' => function($q) {
                    $q->with('user:id,full_name')
                      ->orderBy('created_at', 'desc')
                      ->limit(50);
                }
            ])->findOrFail($id);

            // Transform checklists - INCLUDE ALL DOCUMENT FIELDS
            $checklists = $case->checklists->map(function($item) {
                return [
                    'id' => $item->id,
                    'task' => $item->task,
                    'document_type_id' => $item->document_type_id,
                    'document_type' => $item->document_type,
                    'document_category' => $item->document_category,
                    'document_color' => $item->document_color,
                    'status' => $item->status,
                    'due_date' => $item->due_date,
                    'assigned_clerk_id' => $item->assigned_clerk_id,
                    'assigned_to' => $item->assigned_to,
                    'notes' => $item->notes,
                    'is_out' => $item->is_out,
                    'completed_at' => $item->completed_at,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];
            });

            // Transform folder movements
            $folderMovements = $case->folderMovements->map(function($item) {
                return [
                    'id' => $item->id,
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

            // Transform checklist movements
            $checklistMovements = $case->checklistMovements->map(function($item) {
                return [
                    'id' => $item->id,
                    'checklist_id' => $item->checklist_id,
                    'task_name' => $item->task_name ?? $item->checklist?->task,
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

            // Transform stage history
            $stageHistory = $case->stageHistories->map(function($item) {
                return [
                    'id' => $item->id,
                    'from_stage' => $item->fromStage?->name,
                    'to_stage' => $item->toStage?->name,
                    'changed_by' => $item->changedBy?->full_name,
                    'remarks' => $item->remarks,
                    'created_at' => $item->created_at,
                ];
            });

            // Transform activity logs
            $activityLogs = $case->activityLogs->map(function($item) {
                return [
                    'id' => $item->id,
                    'user' => $item->user?->full_name ?? 'System',
                    'action' => $item->action,
                    'details' => json_decode($item->details, true),
                    'created_at' => $item->created_at,
                ];
            });

            return response()->json([
                'data' => [
                    // Case basic info
                    'id' => $case->id,
                    'case_code' => $case->case_code,
                    'case_no' => $case->case_no,
                    'title' => $case->title,
                    'category_id' => $case->category_id,
                    'category' => $case->category?->name ?? '—',
                    'category_color' => $case->category?->color ?? '#1a4972',
                    'client_id' => $case->client_id,
                    'client' => $case->client?->full_name ?? '—',
                    'client_details' => $case->client ? [
                        'contact_no' => $case->client->contact_no,
                        'email' => $case->client->email,
                        'address' => $case->client->address,
                    ] : null,
                    'court_or_office' => $case->court_or_office,
                    'docket_no' => $case->docket_no,
                    'assigned_lawyer_id' => $case->assigned_lawyer_id,
                    'lawyer' => $case->lawyer?->full_name ?? '—',
                    'assigned_clerk_id' => $case->assigned_clerk_id,
                    'clerk' => $case->clerk?->full_name ?? '—',
                    'priority' => $case->priority,
                    'case_status' => $case->case_status,
                    'current_stage_id' => $case->current_stage_id,
                    'stage' => $case->currentStage?->name ?? '—',
                    'stage_color' => $case->currentStage?->color ?? '#64748b',
                    'summary' => $case->summary,
                    'is_out' => $case->is_out,
                    'created_by' => $case->creator?->full_name ?? '—',
                    'created_at' => $case->created_at,
                    'updated_at' => $case->updated_at,
                    
                    // Related data - ALL LOADED IN ONE GO
                    'checklists' => $checklists,
                    'folder_movements' => $folderMovements,
                    'checklist_movements' => $checklistMovements,
                    'stage_history' => $stageHistory,
                    'activity_logs' => $activityLogs,
                    
                    // Summary counts
                    'summary' => [
                        'total_checklists' => $checklists->count(),
                        'completed_checklists' => $checklists->where('status', 'done')->count(),
                        'pending_checklists' => $checklists->where('status', '!=', 'done')->count(),
                        'out_checklists' => $checklists->where('is_out', true)->count(),
                        'folder_status' => $case->is_out ? 'OUT' : 'IN',
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Case not found',
                'errors' => ['id' => ['Case not found']]
            ], 404);
        }
    }

    /**
     * Update the specified case.
     */
    public function update(Request $request, $id)
    {
        try {
            $case = Cases::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'case_no' => 'required|string|max:180|unique:cases,case_no,' . $id,
                'title' => 'required|string|max:200',
                'category_id' => 'nullable|exists:case_categories,id',
                'client_id' => 'required|exists:clients,id',
                'court_or_office' => 'nullable|string|max:180',
                'docket_no' => 'nullable|string|max:80',
                'assigned_lawyer_id' => 'required|exists:users,id',
                'assigned_clerk_id' => 'nullable|exists:users,id',
                'priority' => 'required|in:low,normal,urgent',
                'case_status' => 'required|in:active,closed,archived',
                'current_stage_id' => 'nullable|exists:case_stages,id',
                'summary' => 'nullable|string|max:2000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $oldStageId = $case->current_stage_id;
            
            $case->update([
                'case_no' => $request->case_no,
                'title' => $request->title,
                'category_id' => $request->category_id,
                'client_id' => $request->client_id,
                'court_or_office' => $request->court_or_office,
                'docket_no' => $request->docket_no,
                'assigned_lawyer_id' => $request->assigned_lawyer_id,
                'assigned_clerk_id' => $request->assigned_clerk_id,
                'priority' => $request->priority,
                'case_status' => $request->case_status,
                'current_stage_id' => $request->current_stage_id,
                'summary' => $request->summary,
            ]);

            // Log stage change if stage changed
            if ($request->current_stage_id && $request->current_stage_id != $oldStageId) {
                CaseStageHistory::create([
                    'case_id' => $case->id,
                    'from_stage_id' => $oldStageId,
                    'to_stage_id' => $request->current_stage_id,
                    'changed_by' => auth()->id(),
                    'remarks' => 'Stage updated via case edit',
                ]);

                CaseActivityLog::create([
                    'case_id' => $case->id,
                    'user_id' => auth()->id(),
                    'action' => 'changed_stage',
                    'details' => json_encode([
                        'from' => $oldStageId,
                        'to' => $request->current_stage_id
                    ]),
                ]);
            }

            // Log general update
            CaseActivityLog::create([
                'case_id' => $case->id,
                'user_id' => auth()->id(),
                'action' => 'updated_case',
                'details' => json_encode([
                    'fields' => array_keys($request->all())
                ]),
            ]);

            DB::commit();

            $case->load([
                'category:id,name,color',
                'client:id,full_name',
                'lawyer:id,full_name',
                'clerk:id,full_name',
                'currentStage:id,name,color'
            ]);

            return response()->json([
                'message' => 'Case updated successfully',
                'data' => [
                    'id' => $case->id,
                    'case_code' => $case->case_code,
                    'case_no' => $case->case_no,
                    'title' => $case->title,
                    'category_id' => $case->category_id,
                    'category' => $case->category?->name ?? '—',
                    'category_color' => $case->category?->color ?? '#1a4972',
                    'client_id' => $case->client_id,
                    'client' => $case->client?->full_name ?? '—',
                    'court_or_office' => $case->court_or_office,
                    'docket_no' => $case->docket_no,
                    'assigned_lawyer_id' => $case->assigned_lawyer_id,
                    'lawyer' => $case->lawyer?->full_name ?? '—',
                    'assigned_clerk_id' => $case->assigned_clerk_id,
                    'clerk' => $case->clerk?->full_name ?? '—',
                    'priority' => $case->priority,
                    'case_status' => $case->case_status,
                    'current_stage_id' => $case->current_stage_id,
                    'stage' => $case->currentStage?->name ?? '—',
                    'stage_color' => $case->currentStage?->color ?? '#64748b',
                    'summary' => $case->summary,
                    'is_out' => $case->is_out,
                    'created_at' => $case->created_at,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update case',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Remove the specified case.
     */
    public function destroy($id)
    {
        try {
            $case = Cases::findOrFail($id);

            DB::beginTransaction();

            // Log deletion
            CaseActivityLog::create([
                'case_id' => $case->id,
                'user_id' => auth()->id(),
                'action' => 'deleted_case',
                'details' => json_encode([
                    'case_code' => $case->case_code,
                    'title' => $case->title
                ]),
            ]);

            // Delete related records
            $case->checklists()->delete();
            $case->folderMovements()->delete();
            $case->checklistMovements()->delete();
            $case->stageHistories()->delete();
            $case->activityLogs()->delete();
            
            // Delete case
            $case->delete();

            DB::commit();

            return response()->json([
                'message' => 'Case deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete case',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Archive case (soft delete or status change)
     */
    public function archive($id)
    {
        try {
            $case = Cases::findOrFail($id);
            
            $case->update(['case_status' => 'archived']);

            CaseActivityLog::create([
                'case_id' => $case->id,
                'user_id' => auth()->id(),
                'action' => 'archived_case',
                'details' => null,
            ]);

            return response()->json([
                'message' => 'Case archived successfully',
                'data' => ['id' => $case->id, 'case_status' => 'archived']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to archive case',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Get activity logs for a case
     */
    public function getActivityLogs($id)
    {
        try {
            $logs = CaseActivityLog::with('user:id,full_name')
                ->where('case_id', $id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($log) {
                    return [
                        'id' => $log->id,
                        'user' => $log->user?->full_name ?? 'System',
                        'action' => $log->action,
                        'details' => json_decode($log->details, true),
                        'created_at' => $log->created_at,
                    ];
                });

            return response()->json(['data' => $logs]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch activity logs',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }
}