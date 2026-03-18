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
use App\Models\Court;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;

class CaseController extends Controller
{
    /**
     * Display a listing of cases with filters and pagination.
     */
/**
 * Display a listing of cases with filters and pagination.
 * OPTIMIZED for millions of records - uses database pagination and query optimization
 */
public function index(Request $request)
{
    try {
        // Start with a base query - select only needed fields
        $query = Cases::query();
        
        // Select only the fields we need, not everything
        $query->select([
            'id', 'case_code', 'case_no', 'title', 'category_id', 
            'client_id', 'court_or_office', 'docket_no', 
            'assigned_lawyer_id', 'assigned_clerk_id', 
            'priority', 'case_status', 'current_stage_id',
            'summary', 'is_out', 'created_at', 'updated_at'
        ]);

        // Eager load relationships with specific fields only
        $query->with([
            'category:id,name,color',
            'client:id,full_name',
            'lawyer:id,full_name',
            'clerk:id,full_name',
            'currentStage:id,name,color'
        ]);

        // Apply filters - these use WHERE clauses which are efficient
        if ($request->filled('case_status')) {
            $query->where('case_status', $request->case_status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('stage_id')) {
            $query->where('current_stage_id', $request->stage_id);
        }

        if ($request->filled('assigned_lawyer_id')) {
            $query->where('assigned_lawyer_id', $request->assigned_lawyer_id);
        }

        if ($request->filled('assigned_clerk_id')) {
            $query->where('assigned_clerk_id', $request->assigned_clerk_id);
        }

        // OPTIMIZED SEARCH - use database search, not PHP
        if ($request->filled('search') && strlen($request->search) >= 2) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                // Use LIKE but with prefix matching for better performance
                $q->where('case_code', 'like', $search . '%')
                  ->orWhere('case_no', 'like', $search . '%')
                  ->orWhere('title', 'like', '%' . $search . '%')
                  ->orWhereHas('client', function($clientQuery) use ($search) {
                      $clientQuery->where('full_name', 'like', $search . '%');
                  });
            });
        }

        // Sorting - always use database sorting
        $sortField = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        // Whitelist allowed sort fields to prevent SQL injection
        $allowedSorts = ['case_code', 'case_no', 'title', 'priority', 'case_status', 'created_at'];
        $sortField = in_array($sortField, $allowedSorts) ? $sortField : 'created_at';
        
        $query->orderBy($sortField, $sortDirection);

        // PAGINATION - THIS IS CRITICAL: only load 25 records at a time
        $perPage = $request->get('per_page', 25);
        
        // Use simplePaginate for large datasets (no count query)
        // or use paginate if you need total count
        $cases = $query->paginate($perPage);

        // Transform data - only transform the current page
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
        \Log::error('Case index error: ' . $e->getMessage());
        
        return response()->json([
            'message' => 'Failed to fetch cases',
            'error' => $e->getMessage()
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
                'courts' => Court::where('is_active', true)
                    ->orderBy('name')
                    ->get(['id', 'name', 'type', 'address']),
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
                'error' => $e->getMessage()
            ], 500);
        }
    }
/**
 * Store a newly created case.
 */
public function store(Request $request)  // ← FIXED: Only needs 1 parameter
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

        // Load relationships for message
        $case->load(['client', 'lawyer', 'clerk', 'category', 'currentStage']);

        // Create stage history if stage is set
        if ($request->current_stage_id) {
            $stageName = CaseStage::find($request->current_stage_id)?->name ?? 'Unknown';
            
            CaseStageHistory::create([
                'case_id' => $case->id,
                'from_stage_id' => null,
                'to_stage_id' => $request->current_stage_id,
                'changed_by' => auth()->id(),
                'remarks' => "Initial stage: {$stageName}",
            ]);
        }

        // Create activity log
        CaseActivityLog::create([
            'case_id' => $case->id,
            'user_id' => auth()->id(),
            'action' => 'created_case',
            'details' => json_encode([
                'case_no' => $case->case_no,
                'title' => $case->title,
            ]),
        ]);

        // Notify assigned lawyer
        if ($case->assigned_lawyer_id) {
            Notification::create([
                'user_id' => $case->assigned_lawyer_id,
                'notifiable_type' => Cases::class,
                'notifiable_id' => $case->id,
                'type' => 'case_assigned',
                'title' => 'New Case Assigned',
                'message' => "Case {$case->case_code} has been assigned to you",
                'data' => [
                    'case_code' => $case->case_code,
                    'case_no' => $case->case_no,
                    'title' => $case->title,
                ],
                'action_url' => "/casemaster"
            ]);
        }

        // Notify assigned clerk
        if ($case->assigned_clerk_id) {
            Notification::create([
                'user_id' => $case->assigned_clerk_id,
                'notifiable_type' => Cases::class,
                'notifiable_id' => $case->id,
                'type' => 'case_assigned',
                'title' => 'New Case Assigned',
                'message' => "Case {$case->case_code} has been assigned to you",
                'data' => [
                    'case_code' => $case->case_code,
                    'case_no' => $case->case_no,
                    'title' => $case->title,
                ],
                'action_url' => "/casemaster"
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
            'error' => $e->getMessage()
        ], 500);
    }
}
    /**
     * Display the specified case with all related data.
     */
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
            'checklists' => function($q) {
                $q->with('document:id,type,category,color')
                  ->orderBy('created_at', 'desc');
            },
            'folderMovements' => function($q) {
                $q->with(['recorder:id,full_name', 'approver:id,full_name'])
                  ->orderBy('created_at', 'desc');
            },
            'checklistMovements' => function($q) {
                $q->with([
                    'checklist:id,document_type_id,status,due_date,assigned_to,notes,is_out',
                    'checklist.document:id,type,category,color',
                    'recorder:id,full_name',
                    'approver:id,full_name'
                ])->orderBy('created_at', 'desc');
            },
            'stageHistories' => function($q) {
                $q->with(['fromStage:id,name', 'toStage:id,name', 'changedBy:id,full_name'])
                  ->orderBy('created_at', 'desc');
            },
            'activityLogs' => function($q) {
                $q->with('user:id,full_name')
                  ->orderBy('created_at', 'desc')
                  ->limit(50);
            }
        ])->find($id);

        if (!$case) {
            return response()->json([
                'message' => 'Case not found',
                'errors' => ['id' => ['Case not found']]
            ], 404);
        }

        // Transform the checklists - NO 'task' field
        $transformedChecklists = $case->checklists->map(function($item) {
            return [
                'id' => $item->id,
                'case_id' => $item->case_id,
                'document_type_id' => $item->document_type_id,
                'document_type' => $item->document?->type,
                'document_category' => $item->document?->category,
                'document_color' => $item->document?->color ?? '#94a3b8',
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

        // Transform the data
        $transformedCase = [
            'id' => $case->id,
            'case_code' => $case->case_code,
            'case_no' => $case->case_no,
            'title' => $case->title,
            'category_id' => $case->category_id,
            'category' => $case->category?->name ?? '—',
            'category_color' => $case->category?->color ?? '#1a4972',
            'client_id' => $case->client_id,
            'client' => $case->client?->full_name ?? '—',
            'assigned_lawyer_id' => $case->assigned_lawyer_id,
            'lawyer' => $case->lawyer?->full_name ?? '—',
            'assigned_clerk_id' => $case->assigned_clerk_id,
            'clerk' => $case->clerk?->full_name ?? '—',
            'court_or_office' => $case->court_or_office,
            'docket_no' => $case->docket_no,
            'priority' => $case->priority,
            'case_status' => $case->case_status,
            'current_stage_id' => $case->current_stage_id,
            'stage' => $case->currentStage?->name ?? '—',
            'stage_color' => $case->currentStage?->color ?? '#64748b',
            'summary' => $case->summary,
            'is_out' => $case->is_out,
            'created_by' => $case->creator?->full_name ?? '—',
            'created_at' => $case->created_at,
            'checklists' => $transformedChecklists,
            'folder_movements' => $case->folderMovements,
            'checklist_movements' => $case->checklistMovements,
            'activity_logs' => $case->activityLogs,
        ];

        return response()->json([
            'data' => $transformedCase
        ]);

    } catch (\Exception $e) {
        
        return response()->json([
            'message' => 'Failed to fetch case details',
            'error' => $e->getMessage()
        ], 500);
    }
}
    /**
     * Update the specified case.
     */
    public function update(Request $request, $id)
    {
        try {
            $case = Cases::find($id);
            
            if (!$case) {
                return response()->json([
                    'message' => 'Case not found',
                    'error' => 'No case found with ID ' . $id
                ], 404);
            }

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

            $oldData = [
                'case_no' => $case->case_no,
                'title' => $case->title,
                'assigned_lawyer_id' => $case->assigned_lawyer_id,
                'assigned_clerk_id' => $case->assigned_clerk_id,
                'current_stage_id' => $case->current_stage_id,
            ];

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

            // Check if stage changed
            if ($request->current_stage_id && $request->current_stage_id != $oldData['current_stage_id']) {
                $oldStage = CaseStage::find($oldData['current_stage_id'])?->name ?? 'None';
                $newStage = CaseStage::find($request->current_stage_id)?->name ?? 'Unknown';
                
                CaseStageHistory::create([
                    'case_id' => $case->id,
                    'from_stage_id' => $oldData['current_stage_id'],
                    'to_stage_id' => $request->current_stage_id,
                    'changed_by' => auth()->id(),
                    'remarks' => "Stage changed from {$oldStage} to {$newStage}",
                ]);
            }

            // Log activity
            CaseActivityLog::create([
                'case_id' => $case->id,
                'user_id' => auth()->id(),
                'action' => 'updated_case',
                'details' => json_encode([
                    'case_no' => $case->case_no,
                    'title' => $case->title,
                ]),
            ]);

            // Notify if lawyer changed
            if ($request->assigned_lawyer_id && $request->assigned_lawyer_id != $oldData['assigned_lawyer_id']) {
                Notification::create([
                    'user_id' => $request->assigned_lawyer_id,
                    'notifiable_type' => Cases::class,
                    'notifiable_id' => $case->id,
                    'type' => 'case_reassigned',
                    'title' => 'Case Reassigned',
                    'message' => "Case {$case->case_code} has been reassigned to you",
                    'data' => json_encode([
                        'case_code' => $case->case_code,
                        'case_no' => $case->case_no,
                        'title' => $case->title,
                    ]),
                    'action_url' => "/casemaster"
                ]);
            }

            // Notify if clerk changed
            if ($request->assigned_clerk_id && $request->assigned_clerk_id != $oldData['assigned_clerk_id']) {
                Notification::create([
                    'user_id' => $request->assigned_clerk_id,
                    'notifiable_type' => Cases::class,
                    'notifiable_id' => $case->id,
                    'type' => 'case_reassigned',
                    'title' => 'Case Reassigned',
                    'message' => "Case {$case->case_code} has been reassigned to you",
                    'data' => json_encode([
                        'case_code' => $case->case_code,
                        'case_no' => $case->case_no,
                        'title' => $case->title,
                    ]),
                    'action_url' => "/casemaster"
                ]);
            }

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
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update case',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified case.
     */
    public function destroy($id)
    {
        try {
            $case = Cases::find($id);
            
            if (!$case) {
                return response()->json([
                    'message' => 'Case not found',
                    'error' => 'No case found with ID ' . $id
                ], 404);
            }

            DB::beginTransaction();

            // Log activity before deletion
            CaseActivityLog::create([
                'case_id' => $case->id,
                'user_id' => auth()->id(),
                'action' => 'deleted_case',
                'details' => json_encode([
                    'case_code' => $case->case_code,
                    'title' => $case->title,
                    'case_no' => $case->case_no,
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
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Archive case
     */
    public function archive($id)
    {
        try {
            $case = Cases::find($id);
            
            if (!$case) {
                return response()->json([
                    'message' => 'Case not found',
                    'error' => 'No case found with ID ' . $id
                ], 404);
            }
            
            $oldStatus = $case->case_status;
            $case->update(['case_status' => 'archived']);

            CaseActivityLog::create([
                'case_id' => $case->id,
                'user_id' => auth()->id(),
                'action' => 'archived_case',
                'details' => json_encode([
                    'from_status' => ucfirst($oldStatus),
                    'to_status' => 'Archived',
                ]),
            ]);

            return response()->json([
                'message' => 'Case archived successfully',
                'data' => ['id' => $case->id, 'case_status' => 'archived']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to archive case',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get activity logs for a case
     */
    public function getActivityLogs($id)
    {
        try {
            $case = Cases::find($id);
            
            if (!$case) {
                return response()->json([
                    'message' => 'Case not found',
                    'error' => 'No case found with ID ' . $id
                ], 404);
            }

            $logs = CaseActivityLog::with('user:id,full_name')
                ->where('case_id', $id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($log) {
                    $details = $log->details;
                    if (is_string($details)) {
                        $decoded = json_decode($details, true);
                        $details = is_array($decoded) ? $decoded : $details;
                    }
                    
                    return [
                        'id' => $log->id,
                        'user' => $log->user?->full_name ?? 'System',
                        'action' => $log->action,
                        'details' => $details,
                        'created_at' => $log->created_at?->format('Y-m-d H:i:s'),
                    ];
                });

            return response()->json(['data' => $logs]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch activity logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}