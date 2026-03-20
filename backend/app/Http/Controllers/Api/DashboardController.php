<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Cases;
use App\Models\Client;
use App\Models\User;
use App\Models\Role;
use App\Models\ChecklistMovement;
use App\Models\FolderMovement;
use App\Models\Document;
use App\Models\LoginLog;
use App\Models\CaseActivityLog;
use App\Models\CaseChecklist;
use App\Models\Hearing;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get dashboard data - Optimized with Eloquent Models
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
        
        // Cache miss - fetch fresh data using models
        $data = match($role) {
            'admin' => $this->getAdminDashboard($userId),
            'lawyer' => $this->getLawyerDashboard($userId),
            'clerk' => $this->getClerkDashboard($userId),
            default => $this->getAdminDashboard($userId)
        };
        
        // Cache for 30 seconds
        Cache::put($cacheKey, $data, 30);
        
        return response()->json($data);
    }


    
    /**
     * Get upcoming hearings for a user based on role
     */
    private function getUpcomingHearings($userId, $role)
    {
        $query = Hearing::with([
            
            'case:id,case_code,title,client_id',
            'case.client:id,full_name',
            'court:id,name',
            'assignedTo:id,full_name'
        ]);

        // Filter by role
        if ($role === 'lawyer') {
            $query->where(function($q) use ($userId) {
                $q->where('created_by', $userId)
                  ->orWhere('assigned_to', $userId)
                  ->orWhereHas('case', function($caseQuery) use ($userId) {
                      $caseQuery->where('assigned_lawyer_id', $userId);
                  });
            });
        } elseif ($role === 'clerk') {
            $query->where(function($q) use ($userId) {
                $q->where('created_by', $userId)
                  ->orWhere('assigned_to', $userId)
                  ->orWhereHas('case', function($caseQuery) use ($userId) {
                      $caseQuery->where('assigned_clerk_id', $userId);
                  });
            });
        }
        // Admin sees all hearings (no filter needed)

        $today = Carbon::today();
        
        // Get upcoming hearings (today and future)
        $hearings = $query->where('hearing_date', '>=', $today)
                         ->where('status', '!=', 'cancelled')
                         ->orderBy('hearing_date')
                         ->orderBy('start_time')
                         ->take(10)
                         ->get()
                         ->map(function($hearing) use ($today) {
                             $hearingDate = Carbon::parse($hearing->hearing_date);
                             $daysUntil = $today->diffInDays($hearingDate, false);
                             
                             // Color coding logic
                             $urgency = 'future'; // green
                             if ($daysUntil === 0) {
                                 $urgency = 'today'; // red
                             } elseif ($daysUntil <= 3) {
                                 $urgency = 'soon'; // yellow/amber
                             }
                             
                             return [
                                 'id' => $hearing->id,
                                 'title' => $hearing->title,
                                 'description' => $hearing->description,
                                 'hearing_date' => $hearing->hearing_date->format('Y-m-d'),
                                 'start_time' => $hearing->start_time ? $hearing->start_time->format('H:i') : null,
                                 'location' => $hearing->location,
                                 'type' => $hearing->type,
                                 'status' => $hearing->status,
                                 'urgency' => $urgency,
                                 'days_until' => $daysUntil,
                                 'case_code' => $hearing->case?->case_code,
                                 'case_title' => $hearing->case?->title,
                                 'client_name' => $hearing->case?->client?->full_name,
                                 'court_name' => $hearing->court?->name,
                                 'assigned_to_name' => $hearing->assignedTo?->full_name
                             ];
                         });

        // Group by urgency for easy access
        return [
            'all' => $hearings,
            'today' => $hearings->where('urgency', 'today')->values(),
            'soon' => $hearings->where('urgency', 'soon')->values(),
            'future' => $hearings->where('urgency', 'future')->values()
        ];
    }

    /**
     * Get hearing stats for calendar
     */
    private function getHearingStats($userId, $role)
    {
        $query = Hearing::query();

        // Filter by role
        if ($role === 'lawyer') {
            $query->where(function($q) use ($userId) {
                $q->where('created_by', $userId)
                  ->orWhere('assigned_to', $userId)
                  ->orWhereHas('case', function($caseQuery) use ($userId) {
                      $caseQuery->where('assigned_lawyer_id', $userId);
                  });
            });
        } elseif ($role === 'clerk') {
            $query->where(function($q) use ($userId) {
                $q->where('created_by', $userId)
                  ->orWhere('assigned_to', $userId)
                  ->orWhereHas('case', function($caseQuery) use ($userId) {
                      $caseQuery->where('assigned_clerk_id', $userId);
                  });
            });
        }

        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();
        $weekEnd = Carbon::today()->endOfWeek();

        return [
            'today' => (clone $query)->whereDate('hearing_date', $today)->where('status', '!=', 'cancelled')->count(),
            'tomorrow' => (clone $query)->whereDate('hearing_date', $tomorrow)->where('status', '!=', 'cancelled')->count(),
            'this_week' => (clone $query)->whereBetween('hearing_date', [$today, $weekEnd])->where('status', '!=', 'cancelled')->count(),
            'upcoming' => (clone $query)->where('hearing_date', '>=', $today)->where('status', '!=', 'cancelled')->count()
        ];
    }

    /**
     * Admin Dashboard - Using Eloquent Models with counts
     */
    private function getAdminDashboard($userId)
    {
        // Get role IDs once
        $lawyerRoleId = Role::where('name', 'lawyer')->value('id');
        $clerkRoleId = Role::where('name', 'clerk')->value('id');
        
        // Using models with count relationships - single queries
        $stats = [
            'total_cases' => Cases::count(),
            'active_cases' => Cases::where('case_status', 'active')->count(),
            'total_clients' => Client::count(),
            'total_lawyers' => User::where('role_id', $lawyerRoleId)->count(),
            'total_clerks' => User::where('role_id', $clerkRoleId)->count(),
            'pending_checklists' => ChecklistMovement::where('approval_status', 'PENDING')->count(),
            'pending_folders' => FolderMovement::where('approval_status', 'PENDING')->count(),
            'pending_documents' => Document::where('requires_approval', 1)
                                        ->where('approval_status', 'pending')
                                        ->count()
        ];

        // Recent activities using models with relationships
        $recentActivities = collect()
            ->merge(
                LoginLog::latest()
                    ->take(5)
                    ->get()
                    ->map(fn($log) => [
                        'type' => 'system',
                        'action' => $log->action,
                        'created_at' => $log->created_at,
                        'user_name' => $log->email_attempted,
                        'case_code' => null,
                        'case_title' => null
                    ])
            )
            ->merge(
                CaseActivityLog::with(['user', 'case'])
                    ->latest()
                    ->take(5)
                    ->get()
                    ->map(fn($log) => [
                        'type' => 'case',
                        'action' => $log->action,
                        'created_at' => $log->created_at,
                        'user_name' => $log->user?->full_name,
                        'case_code' => $log->case?->case_code,
                        'case_title' => $log->case?->title
                    ])
            )
            ->sortByDesc('created_at')
            ->take(10)
            ->values()
            ->toArray();

        // Get hearings data
        $hearings = $this->getUpcomingHearings($userId, 'admin');
        $hearingStats = $this->getHearingStats($userId, 'admin');

        return [
            'stats' => [
                'total_cases' => $stats['total_cases'],
                'active_cases' => $stats['active_cases'],
                'total_clients' => $stats['total_clients'],
                'pending_approvals' => $stats['pending_checklists'] + $stats['pending_folders']
            ],
            'adminStats' => [
                'total_users' => $stats['total_lawyers'] + $stats['total_clerks'],
                'lawyers' => $stats['total_lawyers'],
                'clerks' => $stats['total_clerks'],
                'pending_documents' => $stats['pending_documents'],
                'pending_movements' => $stats['pending_checklists'] + $stats['pending_folders'],
                'pending_total' => $stats['pending_documents'] + $stats['pending_checklists'] + $stats['pending_folders']
            ],
            'recentActivities' => $recentActivities,
            'upcomingHearings' => $hearings['all'],
            'hearingStats' => $hearingStats
        ];
    }

    /**
     * Lawyer Dashboard - Using Eloquent Models with relationships
     */
    private function getLawyerDashboard($userId)
    {
        // Get counts using models
        $totalCases = Cases::where('assigned_lawyer_id', $userId)->count();
        $activeCases = Cases::where('assigned_lawyer_id', $userId)
                           ->where('case_status', 'active')
                           ->count();
        
        $pendingChecklists = ChecklistMovement::where('approval_status', 'PENDING')->count();
        $pendingFolders = FolderMovement::where('approval_status', 'PENDING')->count();
        $pendingDocuments = Document::where('requires_approval', 1)
                                   ->where('approval_status', 'pending')
                                   ->count();

        // Recent cases with eager loading
        $recentCases = Cases::with(['client', 'currentStage'])
            ->where('assigned_lawyer_id', $userId)
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($case) => [
                'id' => $case->id,
                'case_code' => $case->case_code,
                'title' => $case->title,
                'priority' => $case->priority,
                'case_status' => $case->case_status,
                'client' => $case->client?->full_name,
                'stage' => $case->currentStage?->name
            ]);

        $pendingTotal = $pendingDocuments + $pendingChecklists + $pendingFolders;

        // Get hearings data
        $hearings = $this->getUpcomingHearings($userId, 'lawyer');
        $hearingStats = $this->getHearingStats($userId, 'lawyer');

        return [
            'lawyerStats' => [
                'assigned_cases' => $totalCases,
                'active_cases' => $activeCases,
                'pending_approvals' => $pendingTotal
            ],
            'myCases' => $recentCases,
            'pendingItems' => [
                'documents' => $pendingDocuments,
                'movements' => $pendingChecklists + $pendingFolders,
                'total' => $pendingTotal
            ],
            'upcomingHearings' => $hearings['all'],
            'hearingStats' => $hearingStats
        ];
    }

    /**
     * Clerk Dashboard - Using Eloquent Models with relationships
     */
    private function getClerkDashboard($userId)
    {
        // Get stats using models
        $assignedCases = Cases::where('assigned_clerk_id', $userId)->count();
        
        $totalTasks = CaseChecklist::where('assigned_clerk_id', $userId)->count();
        $pendingTasks = CaseChecklist::where('assigned_clerk_id', $userId)
                                    ->where('status', '!=', 'done')
                                    ->count();
        $completedTasks = CaseChecklist::where('assigned_clerk_id', $userId)
                                      ->where('status', 'done')
                                      ->count();

        $dueTasks = CaseChecklist::where('assigned_clerk_id', $userId)
                                    ->where('status', '!=', 'done')
                                    ->whereDate('due_date', '<', now())
                                    ->count();
        $pendingFolders = FolderMovement::where('recorded_by', $userId)
                                    ->where('approval_status', 'PENDING')
                                    ->count();



        // Recent tasks with eager loading
        $recentTasks = CaseChecklist::with('case', 'document')
            ->where('assigned_clerk_id', $userId)
            ->orderByRaw("
                CASE 
                    WHEN status = 'todo' THEN 1
                    WHEN status = 'in-progress' THEN 2
                    ELSE 3
                END
            ")
            ->orderBy('due_date')
            ->take(10)
            ->get()
            ->map(fn($task) => [
                'id' => $task->id,
                'task' => $task->document?->type,
                'status' => $task->status,
                'due_date' => $task->due_date,
                'document_type' => $task->document?->category,
                'case_code' => $task->case?->case_code,
                'case_title' => $task->case?->title
            ]);

        // Get hearings data
        $hearings = $this->getUpcomingHearings($userId, 'clerk');
        $hearingStats = $this->getHearingStats($userId, 'clerk');

        return [
            'clerkStats' => [
                'assigned_cases' => $assignedCases,
                'total_tasks' => $totalTasks,
                'pending_folders' => $pendingFolders,
                'pending_tasks' => $pendingTasks,
                'due_tasks' => $dueTasks,
                'completed_tasks' => $completedTasks
            ],
            'myTasks' => $recentTasks,
            'upcomingHearings' => $hearings['all'],
            'hearingStats' => $hearingStats
        ];
    }

    /**
     * Get detailed case information (for modal)
     */
    public function getCaseDetails($id)
    {
        $case = Cases::with([
            'client',
            'assignedLawyer',
            'assignedClerk',
            'currentStage',
            'checklists' => fn($q) => $q->latest(),
            'documents' => fn($q) => $q->latest(),
            'activities' => fn($q) => $q->latest()->take(10)
        ])->findOrFail($id);

        return response()->json($case);
    }

    /**
     * Get user activities (for modal)
     */
    public function getUserActivities($userId)
    {
        $activities = CaseActivityLog::with('case')
            ->where('user_id', $userId)
            ->latest()
            ->paginate(20);

        return response()->json($activities);
    }

    /**
     * Get pending approvals list (for modal)
     */
    public function getPendingApprovals()
    {
        $documents = Document::with(['case', 'uploadedBy'])
            ->where('requires_approval', 1)
            ->where('approval_status', 'pending')
            ->latest()
            ->get();

        $checklists = ChecklistMovement::with(['checklist', 'case', 'requestedBy'])
            ->where('approval_status', 'PENDING')
            ->latest()
            ->get();

        $folders = FolderMovement::with(['folder', 'case', 'requestedBy'])
            ->where('approval_status', 'PENDING')
            ->latest()
            ->get();

        return response()->json([
            'documents' => $documents,
            'checklists' => $checklists,
            'folders' => $folders
        ]);
    }
}