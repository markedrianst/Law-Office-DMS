<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LoginLog;
use App\Models\CaseActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AuditLogController extends Controller
{
    /**
     * Get system audit logs (login attempts, user actions)
     */
    public function index(Request $request)
    {
        try {
            $query = LoginLog::query()
                ->leftJoin('users', 'login_logs.user_id', '=', 'users.id')
                ->select(
                    'login_logs.*',
                    'users.full_name as user_name',
                    'users.email as user_email',
                    'users.id as related_user_id'
                );

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('login_logs.email_attempted', 'like', "%{$search}%")
                      ->orWhere('users.full_name', 'like', "%{$search}%")
                      ->orWhere('login_logs.ip_address', 'like', "%{$search}%");
                });
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('login_logs.status', $request->status);
            }

            // Filter by action
            if ($request->filled('action')) {
                $query->where('login_logs.action', $request->action);
            }

            // Date range filters
            if ($request->filled('date_from')) {
                $query->whereDate('login_logs.created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('login_logs.created_at', '<=', $request->date_to);
            }

            // Sorting
            $query->orderBy('login_logs.created_at', 'desc');

            // Pagination
            $perPage = $request->get('per_page', 15);
            $logs = $query->paginate($perPage);

            // Transform data
            $transformed = collect($logs->items())->map(function ($log) {
                return [
                    'id' => $log->id,
                    'type' => 'system',
                    'user' => $log->related_user_id ? [
                        'id' => $log->related_user_id,
                        'name' => $log->user_name,
                        'email' => $log->user_email,
                    ] : null,
                    'email_attempted' => $log->email_attempted,
                    'action' => $log->action,
                    'status' => $log->status,
                    'ip_address' => $log->ip_address,
                    'user_agent' => $log->user_agent,
                    'details' => $log->details,
                    'created_at' => $log->created_at,
                ];
            });

            // Get stats
            $stats = [
                'total' => LoginLog::count(),
                'success' => LoginLog::where('status', 'success')->count(),
                'failed' => LoginLog::where('status', 'failed')->count(),
                'logins' => LoginLog::where('action', 'login')->count(),
                'logouts' => LoginLog::where('action', 'logout')->count(),
                'password_changes' => LoginLog::where('action', 'password_change')->count(),
            ];

            return response()->json([
                'data' => $transformed,
                'meta' => [
                    'current_page' => $logs->currentPage(),
                    'last_page' => $logs->lastPage(),
                    'per_page' => $logs->perPage(),
                    'total' => $logs->total(),
                    'from' => $logs->firstItem(),
                    'to' => $logs->lastItem(),
                ],
                'stats' => $stats,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch audit logs',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Get case activity logs
     */
    public function caseActivity(Request $request)
    {
        try {
            $query = CaseActivityLog::with(['user:id,full_name', 'case:id,case_code,title']);

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('full_name', 'like', "%{$search}%");
                    })->orWhereHas('case', function ($caseQuery) use ($search) {
                        $caseQuery->where('case_code', 'like', "%{$search}%")
                                  ->orWhere('title', 'like', "%{$search}%");
                    })->orWhere('action', 'like', "%{$search}%");
                });
            }

            // Filter by action
            if ($request->filled('action')) {
                $query->where('action', $request->action);
            }

            // Filter by case
            if ($request->filled('case_id')) {
                $query->where('case_id', $request->case_id);
            }

            // Date range filters
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Sorting
            $query->orderBy('created_at', 'desc');

            // Pagination
            $perPage = $request->get('per_page', 15);
            $logs = $query->paginate($perPage);

            // Transform data
            $transformed = $logs->map(function ($log) {
                $details = null;
                if ($log->details) {
                    $decoded = json_decode($log->details, true);
                    $details = is_array($decoded) ? $decoded : ['note' => $log->details];
                }

                return [
                    'id' => $log->id,
                    'type' => 'case',
                    'case_id' => $log->case_id,
                    'case_code' => $log->case?->case_code,
                    'case_title' => $log->case?->title,
                    'case_no' => $log->case?->case_no,
                    'actor' => $log->user?->full_name ?? 'System',
                    'user_id' => $log->user_id,
                    'action' => $log->action,
                    'details' => $details,
                    'created_at' => $log->created_at,
                ];
            });

            return response()->json([
                'data' => $transformed,
                'meta' => [
                    'current_page' => $logs->currentPage(),
                    'last_page' => $logs->lastPage(),
                    'per_page' => $logs->perPage(),
                    'total' => $logs->total(),
                    'from' => $logs->firstItem(),
                    'to' => $logs->lastItem(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch case activity logs',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Get combined audit trail (both system and case logs)
     */
    public function combined(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 20);
            $page = $request->get('page', 1);

            // Build system logs query
            $systemQuery = LoginLog::query()
                ->leftJoin('users', 'login_logs.user_id', '=', 'users.id')
                ->select(
                    'login_logs.id',
                    'login_logs.created_at',
                    DB::raw("'system' as type"),
                    'login_logs.action',
                    'login_logs.status',
                    'login_logs.email_attempted',
                    'login_logs.ip_address',
                    'login_logs.user_agent',
                    'login_logs.details',
                    'users.full_name as actor_name',
                    'users.id as user_id',
                    DB::raw("NULL as case_code"),
                    DB::raw("NULL as case_title"),
                    DB::raw("NULL as case_id")
                );

            // Build case logs query
            $caseQuery = CaseActivityLog::query()
                ->leftJoin('users', 'case_activity_logs.user_id', '=', 'users.id')
                ->leftJoin('cases', 'case_activity_logs.case_id', '=', 'cases.id')
                ->select(
                    'case_activity_logs.id',
                    'case_activity_logs.created_at',
                    DB::raw("'case' as type"),
                    'case_activity_logs.action',
                    DB::raw("NULL as status"),
                    DB::raw("NULL as email_attempted"),
                    DB::raw("NULL as ip_address"),
                    DB::raw("NULL as user_agent"),
                    'case_activity_logs.details',
                    'users.full_name as actor_name',
                    'users.id as user_id',
                    'cases.case_code',
                    'cases.title as case_title',
                    'cases.id as case_id'
                );

            // Apply common filters
            if ($request->filled('search')) {
                $search = $request->search;
                $systemQuery->where(function ($q) use ($search) {
                    $q->where('login_logs.email_attempted', 'like', "%{$search}%")
                      ->orWhere('users.full_name', 'like', "%{$search}%");
                });
                $caseQuery->where(function ($q) use ($search) {
                    $q->where('users.full_name', 'like', "%{$search}%")
                      ->orWhere('cases.case_code', 'like', "%{$search}%")
                      ->orWhere('cases.title', 'like', "%{$search}%")
                      ->orWhere('case_activity_logs.action', 'like', "%{$search}%");
                });
            }

            // Filter by type
            if ($request->filled('type')) {
                if ($request->type === 'system') {
                    $caseQuery->whereRaw('1 = 0'); // Return no case logs
                } elseif ($request->type === 'case') {
                    $systemQuery->whereRaw('1 = 0'); // Return no system logs
                }
            }

            // Date range
            if ($request->filled('date_from')) {
                $systemQuery->whereDate('login_logs.created_at', '>=', $request->date_from);
                $caseQuery->whereDate('case_activity_logs.created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $systemQuery->whereDate('login_logs.created_at', '<=', $request->date_to);
                $caseQuery->whereDate('case_activity_logs.created_at', '<=', $request->date_to);
            }

            // Combine and paginate
            $combined = $systemQuery->union($caseQuery);
            
            // Get total count
            $total = DB::table(DB::raw("({$combined->toSql()}) as combined"))
                ->mergeBindings($combined->getQuery())
                ->count();

            // Get paginated results
            $logs = $combined->orderBy('created_at', 'desc')
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get();

            // Transform
            $transformed = $logs->map(function ($log) {
                if ($log->type === 'system') {
                    return [
                        'id' => $log->id,
                        'type' => 'system',
                        'user' => $log->user_id ? [
                            'id' => $log->user_id,
                            'name' => $log->actor_name,
                        ] : null,
                        'email_attempted' => $log->email_attempted,
                        'action' => $log->action,
                        'status' => $log->status,
                        'ip_address' => $log->ip_address,
                        'user_agent' => $log->user_agent,
                        'details' => $log->details,
                        'created_at' => $log->created_at,
                    ];
                } else {
                    $details = null;
                    if ($log->details) {
                        $decoded = json_decode($log->details, true);
                        $details = is_array($decoded) ? $decoded : ['note' => $log->details];
                    }
                    return [
                        'id' => $log->id,
                        'type' => 'case',
                        'case_id' => $log->case_id,
                        'case_code' => $log->case_code,
                        'case_title' => $log->case_title,
                        'actor' => $log->actor_name ?? 'System',
                        'action' => $log->action,
                        'details' => $details,
                        'created_at' => $log->created_at,
                    ];
                }
            });

            return response()->json([
                'data' => $transformed,
                'meta' => [
                    'current_page' => (int) $page,
                    'last_page' => (int) ceil($total / $perPage),
                    'per_page' => (int) $perPage,
                    'total' => (int) $total,
                    'from' => $total === 0 ? 0 : (($page - 1) * $perPage) + 1,
                    'to' => min($page * $perPage, $total),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch combined logs',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Get single log entry
     */
    public function show($id)
    {
        try {
            // Try system logs first
            $log = DB::table('login_logs')
                ->leftJoin('users', 'login_logs.user_id', '=', 'users.id')
                ->select(
                    'login_logs.*',
                    'users.full_name as user_name',
                    'users.email as user_email',
                    'users.id as related_user_id'
                )
                ->where('login_logs.id', $id)
                ->first();

            if ($log) {
                return response()->json([
                    'data' => [
                        'id' => $log->id,
                        'type' => 'system',
                        'user' => $log->related_user_id ? [
                            'id' => $log->related_user_id,
                            'name' => $log->user_name,
                            'email' => $log->user_email,
                        ] : null,
                        'email_attempted' => $log->email_attempted,
                        'action' => $log->action,
                        'status' => $log->status,
                        'ip_address' => $log->ip_address,
                        'user_agent' => $log->user_agent,
                        'details' => $log->details,
                        'created_at' => $log->created_at,
                    ]
                ]);
            }

            // Try case logs
            $caseLog = CaseActivityLog::with(['user:id,full_name', 'case:id,case_code,title'])
                ->find($id);

            if ($caseLog) {
                $details = null;
                if ($caseLog->details) {
                    $decoded = json_decode($caseLog->details, true);
                    $details = is_array($decoded) ? $decoded : ['note' => $caseLog->details];
                }

                return response()->json([
                    'data' => [
                        'id' => $caseLog->id,
                        'type' => 'case',
                        'case_id' => $caseLog->case_id,
                        'case_code' => $caseLog->case?->case_code,
                        'case_title' => $caseLog->case?->title,
                        'actor' => $caseLog->user?->full_name ?? 'System',
                        'action' => $caseLog->action,
                        'details' => $details,
                        'created_at' => $caseLog->created_at,
                    ]
                ]);
            }

            return response()->json([
                'message' => 'Log not found',
                'errors' => ['id' => ['Log not found']]
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch log',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Get available actions for filter dropdown
     */
    public function getActions()
    {
        try {
            $systemActions = LoginLog::distinct()
                ->orderBy('action')
                ->pluck('action')
                ->map(function ($action) {
                    return [
                        'value' => $action,
                        'label' => $this->formatActionLabel($action),
                        'type' => 'system'
                    ];
                });

            $caseActions = CaseActivityLog::distinct()
                ->orderBy('action')
                ->pluck('action')
                ->map(function ($action) {
                    return [
                        'value' => $action,
                        'label' => ucfirst(str_replace('_', ' ', $action)),
                        'type' => 'case'
                    ];
                });

            return response()->json([
                'data' => $systemActions->concat($caseActions)->values()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch actions',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Export logs as CSV
     */
    public function export(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'nullable|in:system,case,combined',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $type = $request->get('type', 'combined');
            
            if ($type === 'system') {
                $logs = $this->getSystemLogsForExport($request);
            } elseif ($type === 'case') {
                $logs = $this->getCaseLogsForExport($request);
            } else {
                $logs = $this->getCombinedLogsForExport($request);
            }

            $filename = 'audit_logs_' . now()->format('Y-m-d_His') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];

            $callback = function () use ($logs, $type) {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

                if ($type === 'system') {
                    fputcsv($file, ['ID', 'Type', 'User', 'Email Attempted', 'Action', 'Status', 'IP Address', 'User Agent', 'Details', 'Date & Time']);
                    foreach ($logs as $log) {
                        fputcsv($file, [
                            $log->id,
                            'System',
                            $log->user_name ?? '—',
                            $log->email_attempted,
                            $log->action,
                            $log->status,
                            $log->ip_address,
                            $this->truncateUserAgent($log->user_agent),
                            $log->details,
                            $log->created_at,
                        ]);
                    }
                } elseif ($type === 'case') {
                    fputcsv($file, ['ID', 'Type', 'Actor', 'Case Code', 'Case Title', 'Action', 'Details', 'Date & Time']);
                    foreach ($logs as $log) {
                        $details = $log->details ? json_decode($log->details, true) : null;
                        $detailsStr = is_array($details) ? json_encode($details) : $log->details;
                        
                        fputcsv($file, [
                            $log->id,
                            'Case',
                            $log->actor_name ?? 'System',
                            $log->case_code ?? '—',
                            $log->case_title ?? '—',
                            $log->action,
                            $detailsStr,
                            $log->created_at,
                        ]);
                    }
                } else {
                    fputcsv($file, ['ID', 'Type', 'Actor/User', 'Action', 'Status', 'Details', 'Date & Time']);
                    foreach ($logs as $log) {
                        $actor = $log->type === 'system' 
                            ? ($log->user_name ?? $log->email_attempted ?? '—')
                            : ($log->actor_name ?? 'System');
                        
                        $details = $log->type === 'system' 
                            ? $log->details
                            : ($log->details ? json_decode($log->details, true) : null);
                        
                        $detailsStr = is_array($details) ? json_encode($details) : $details;

                        fputcsv($file, [
                            $log->id,
                            $log->type === 'system' ? 'System' : 'Case',
                            $actor,
                            $log->action,
                            $log->status ?? '—',
                            $detailsStr,
                            $log->created_at,
                        ]);
                    }
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to export logs',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Get stats for dashboard
     */
    public function getStats()
    {
        try {
            $now = now();
            $today = $now->copy()->startOfDay();

            $stats = [
                'total_logs' => LoginLog::count() + CaseActivityLog::count(),
                'system_logs' => LoginLog::count(),
                'case_logs' => CaseActivityLog::count(),
                'today_logs' => LoginLog::where('created_at', '>=', $today)->count() + 
                               CaseActivityLog::where('created_at', '>=', $today)->count(),
                'login_stats' => [
                    'success' => LoginLog::where('action', 'login')->where('status', 'success')->count(),
                    'failed' => LoginLog::where('action', 'login')->where('status', 'failed')->count(),
                ],
                'recent_actions' => LoginLog::select('action', DB::raw('count(*) as count'))
                    ->where('created_at', '>=', $now->copy()->subDays(7))
                    ->groupBy('action')
                    ->orderBy('count', 'desc')
                    ->limit(5)
                    ->get(),
            ];

            return response()->json(['data' => $stats]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch stats',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    // ==================== PRIVATE HELPERS ====================

    private function getSystemLogsForExport($request)
    {
        $query = LoginLog::query()
            ->leftJoin('users', 'login_logs.user_id', '=', 'users.id')
            ->select(
                'login_logs.*',
                'users.full_name as user_name'
            );

        if ($request->filled('date_from')) {
            $query->whereDate('login_logs.created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('login_logs.created_at', '<=', $request->date_to);
        }

        return $query->orderBy('login_logs.created_at', 'desc')->get();
    }

    private function getCaseLogsForExport($request)
    {
        $query = CaseActivityLog::query()
            ->leftJoin('users', 'case_activity_logs.user_id', '=', 'users.id')
            ->leftJoin('cases', 'case_activity_logs.case_id', '=', 'cases.id')
            ->select(
                'case_activity_logs.*',
                'users.full_name as actor_name',
                'cases.case_code',
                'cases.title as case_title'
            );

        if ($request->filled('date_from')) {
            $query->whereDate('case_activity_logs.created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('case_activity_logs.created_at', '<=', $request->date_to);
        }

        return $query->orderBy('case_activity_logs.created_at', 'desc')->get();
    }

    private function getCombinedLogsForExport($request)
    {
        $systemQuery = LoginLog::query()
            ->leftJoin('users', 'login_logs.user_id', '=', 'users.id')
            ->select(
                'login_logs.id',
                'login_logs.created_at',
                DB::raw("'system' as type"),
                'login_logs.action',
                'login_logs.status',
                'login_logs.email_attempted',
                'login_logs.details',
                'users.full_name as user_name',
                DB::raw("NULL as actor_name"),
                DB::raw("NULL as case_code"),
                DB::raw("NULL as case_title")
            );

        $caseQuery = CaseActivityLog::query()
            ->leftJoin('users', 'case_activity_logs.user_id', '=', 'users.id')
            ->leftJoin('cases', 'case_activity_logs.case_id', '=', 'cases.id')
            ->select(
                'case_activity_logs.id',
                'case_activity_logs.created_at',
                DB::raw("'case' as type"),
                'case_activity_logs.action',
                DB::raw("NULL as status"),
                DB::raw("NULL as email_attempted"),
                'case_activity_logs.details',
                DB::raw("NULL as user_name"),
                'users.full_name as actor_name',
                'cases.case_code',
                'cases.title as case_title'
            );

        if ($request->filled('date_from')) {
            $systemQuery->whereDate('login_logs.created_at', '>=', $request->date_from);
            $caseQuery->whereDate('case_activity_logs.created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $systemQuery->whereDate('login_logs.created_at', '<=', $request->date_to);
            $caseQuery->whereDate('case_activity_logs.created_at', '<=', $request->date_to);
        }

        return $systemQuery->union($caseQuery)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    private function formatActionLabel($action)
    {
        $labels = [
            'login' => 'Login',
            'logout' => 'Logout',
            'password_change' => 'Password Change',
            'user_create' => 'User Created',
            'user_update' => 'User Updated',
            'user_delete' => 'User Deleted',
            'user_create_failed' => 'User Creation Failed',
            'activated' => 'Account Activated',
            'deactivated' => 'Account Deactivated',
        ];

        return $labels[$action] ?? ucfirst(str_replace('_', ' ', $action));
    }

    private function truncateUserAgent($ua)
    {
        if (!$ua) return '—';
        if (strlen($ua) <= 100) return $ua;
        return substr($ua, 0, 100) . '…';
    }
}