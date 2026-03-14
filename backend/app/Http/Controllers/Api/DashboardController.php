<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Get dashboard data - Optimized for speed
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $userId = $user->id;
        $role = strtolower($user->role->name);
        
        // Cache key based on user and role
        $cacheKey = "dashboard:{$role}:{$userId}";
        
        // Try cache first (returns in <1ms)
        $cached = Cache::get($cacheKey);
        if ($cached) {
            return response()->json($cached);
        }
        
        // Cache miss - fetch fresh data
        $data = match($role) {
            'admin' => $this->getAdminDashboard(),
            'lawyer' => $this->getLawyerDashboard($userId),
            'clerk' => $this->getClerkDashboard($userId),
            default => $this->getAdminDashboard()
        };
        
        // Cache for 30 seconds
        Cache::put($cacheKey, $data, 30);
        
        return response()->json($data);
    }

    /**
     * Admin Dashboard - Optimized single query
     */
    private function getAdminDashboard()
    {
        // Single query for all stats
        $stats = DB::selectOne("
            SELECT
                (SELECT COUNT(*) FROM cases) as total_cases,
                (SELECT COUNT(*) FROM cases WHERE case_status = 'active') as active_cases,
                (SELECT COUNT(*) FROM clients) as total_clients,
                (SELECT COUNT(*) FROM users WHERE role_id = (SELECT id FROM roles WHERE name = 'lawyer')) as total_lawyers,
                (SELECT COUNT(*) FROM users WHERE role_id = (SELECT id FROM roles WHERE name = 'clerk')) as total_clerks,
                (SELECT COUNT(*) FROM checklist_movements WHERE approval_status = 'PENDING') as pending_checklists,
                (SELECT COUNT(*) FROM folder_movements WHERE approval_status = 'PENDING') as pending_folders,
                (SELECT COUNT(*) FROM documents WHERE requires_approval = 1 AND approval_status = 'pending') as pending_documents
        ");

        // Recent activities - optimized with UNION
        $recentActivities = DB::select("
            (SELECT 
                'system' as type,
                action,
                created_at,
                email_attempted as user_name,
                NULL as case_code,
                NULL as case_title
             FROM login_logs 
             ORDER BY created_at DESC 
             LIMIT 5)
            UNION ALL
            (SELECT 
                'case' as type,
                action,
                created_at,
                (SELECT full_name FROM users WHERE id = case_activity_logs.user_id) as user_name,
                (SELECT case_code FROM cases WHERE id = case_activity_logs.case_id) as case_code,
                (SELECT title FROM cases WHERE id = case_activity_logs.case_id) as case_title
             FROM case_activity_logs 
             ORDER BY created_at DESC 
             LIMIT 5)
            ORDER BY created_at DESC
            LIMIT 10
        ");

        return [
            'stats' => [
                'total_cases' => (int)($stats->total_cases ?? 0),
                'active_cases' => (int)($stats->active_cases ?? 0),
                'total_clients' => (int)($stats->total_clients ?? 0),
                'pending_approvals' => (int)(($stats->pending_checklists ?? 0) + ($stats->pending_folders ?? 0))
            ],
            'adminStats' => [
                'total_users' => (int)(($stats->total_lawyers ?? 0) + ($stats->total_clerks ?? 0)),
                'lawyers' => (int)($stats->total_lawyers ?? 0),
                'clerks' => (int)($stats->total_clerks ?? 0),
                'pending_documents' => (int)($stats->pending_documents ?? 0),
                'pending_movements' => (int)(($stats->pending_checklists ?? 0) + ($stats->pending_folders ?? 0)),
                'pending_total' => (int)(($stats->pending_documents ?? 0) + ($stats->pending_checklists ?? 0) + ($stats->pending_folders ?? 0))
            ],
            'recentActivities' => $recentActivities
        ];
    }

    /**
     * Lawyer Dashboard - Optimized
     */
    private function getLawyerDashboard($userId)
    {
        $stats = DB::selectOne("
            SELECT
                (SELECT COUNT(*) FROM cases WHERE assigned_lawyer_id = ?) as total_cases,
                (SELECT COUNT(*) FROM cases WHERE assigned_lawyer_id = ? AND case_status = 'active') as active_cases,
                (SELECT COUNT(*) FROM checklist_movements WHERE approval_status = 'PENDING') as pending_checklists,
                (SELECT COUNT(*) FROM folder_movements WHERE approval_status = 'PENDING') as pending_folders,
                (SELECT COUNT(*) FROM documents WHERE requires_approval = 1 AND approval_status = 'pending') as pending_documents
        ", [$userId, $userId]);

        $recentCases = DB::select("
            SELECT 
                c.id, 
                c.case_code, 
                c.title, 
                c.priority,
                c.case_status,
                cl.full_name as client,
                (SELECT name FROM case_stages WHERE id = c.current_stage_id) as stage
            FROM cases c
            LEFT JOIN clients cl ON cl.id = c.client_id
            WHERE c.assigned_lawyer_id = ?
            ORDER BY c.created_at DESC
            LIMIT 5
        ", [$userId]);

        $pendingTotal = ($stats->pending_documents ?? 0) + 
                       ($stats->pending_checklists ?? 0) + 
                       ($stats->pending_folders ?? 0);

        return [
            'lawyerStats' => [
                'assigned_cases' => (int)($stats->total_cases ?? 0),
                'active_cases' => (int)($stats->active_cases ?? 0),
                'pending_approvals' => (int)$pendingTotal
            ],
            'myCases' => $recentCases,
            'pendingItems' => [
                'documents' => (int)($stats->pending_documents ?? 0),
                'movements' => (int)(($stats->pending_checklists ?? 0) + ($stats->pending_folders ?? 0)),
                'total' => (int)$pendingTotal
            ]
        ];
    }

    /**
     * Clerk Dashboard - Optimized
     */
    private function getClerkDashboard($userId)
    {
        $stats = DB::selectOne("
            SELECT
                (SELECT COUNT(*) FROM cases WHERE assigned_clerk_id = ?) as assigned_cases,
                (SELECT COUNT(*) FROM case_checklists WHERE assigned_clerk_id = ?) as total_tasks,
                (SELECT COUNT(*) FROM case_checklists WHERE assigned_clerk_id = ? AND status != 'done') as pending_tasks,
                (SELECT COUNT(*) FROM case_checklists WHERE assigned_clerk_id = ? AND status = 'done') as completed_tasks
        ", [$userId, $userId, $userId, $userId]);

        $recentTasks = DB::select("
            SELECT 
                cc.id, 
                cc.task, 
                cc.status, 
                cc.due_date,
                cc.document_type,
                c.case_code,
                c.title as case_title
            FROM case_checklists cc
            JOIN cases c ON c.id = cc.case_id
            WHERE cc.assigned_clerk_id = ?
            ORDER BY 
                CASE 
                    WHEN cc.status = 'todo' THEN 1
                    WHEN cc.status = 'in-progress' THEN 2
                    ELSE 3
                END,
                cc.due_date ASC
            LIMIT 10
        ", [$userId]);

        return [
            'clerkStats' => [
                'assigned_cases' => (int)($stats->assigned_cases ?? 0),
                'total_tasks' => (int)($stats->total_tasks ?? 0),
                'pending_tasks' => (int)($stats->pending_tasks ?? 0),
                'completed_tasks' => (int)($stats->completed_tasks ?? 0)
            ],
            'myTasks' => $recentTasks
        ];
    }
}