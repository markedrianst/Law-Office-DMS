<?php
// app/Http/Controllers/Admin/HearingController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hearing;
use App\Models\Cases;
use App\Models\User;
use App\Models\Court;
use App\Models\Notification;
use App\Models\CaseActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HearingController extends Controller
{
    /**
     * Get hearings for calendar view
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $role = strtolower($user->role?->name ?? $user->role ?? '');
            
            $query = Hearing::with([
                'case:id,case_code,title,assigned_lawyer_id,assigned_clerk_id',
                'creator:id,full_name',
                'assignedTo:id,full_name',
                'court:id,name'
            ]);

            // Filter by user role
            if ($role === 'lawyer') {
                $query->where(function($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhere('assigned_to', $user->id)
                      ->orWhereHas('case', function($caseQuery) use ($user) {
                          $caseQuery->where('assigned_lawyer_id', $user->id);
                      });
                });
            } elseif ($role === 'clerk') {
                $query->where(function($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhere('assigned_to', $user->id)
                      ->orWhereHas('case', function($caseQuery) use ($user) {
                          $caseQuery->where('assigned_clerk_id', $user->id);
                      });
                });
            }

            // Month/Year filter
            if ($request->filled('month') && $request->filled('year')) {
                $query->whereMonth('hearing_date', $request->month)
                      ->whereYear('hearing_date', $request->year);
            }

            // Type filter
            if ($request->filled('type') && $request->type !== '') {
                $query->where('type', $request->type);
            }

            // Status filter
            if ($request->filled('status') && $request->status !== '') {
                $query->where('status', $request->status);
            }

            // Case filter
            if ($request->filled('case_id') && $request->case_id !== '') {
                $query->where('case_id', $request->case_id);
            }

            // Personal only
            if ($request->boolean('personal_only')) {
                $query->whereNull('case_id')->where('created_by', $user->id);
            }

            $query->orderBy('hearing_date', 'asc')->orderBy('start_time', 'asc');
            
            $hearings = $query->get();

            return response()->json([
                'success' => true,
                'data' => $hearings
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch hearings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $hearing = Hearing::with([
                'case',
                'creator',
                'assignedTo',
                'court',
                'rescheduledFrom',
                'rescheduledTo'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $hearing
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Hearing not found'
            ], 404);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'case_id' => 'nullable|exists:cases,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'hearing_date' => 'required|date|after_or_equal:today',
            'start_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'court_id' => 'nullable|exists:courts,id',
            'type' => 'required|in:hearing,meeting,deadline,task,personal,other',
            'assigned_to' => 'nullable|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = auth()->user();

            $hearing = Hearing::create([
                'case_id' => $request->case_id,
                'title' => $request->title,
                'description' => $request->description,
                'hearing_date' => $request->hearing_date,
                'start_time' => $request->start_time,
                'location' => $request->location,
                'court_id' => $request->court_id,
                'type' => $request->type,
                'status' => 'scheduled',
                'created_by' => $user->id,
                'assigned_to' => $request->assigned_to
            ]);

            $hearing->load(['case', 'assignedTo', 'court', 'creator']);

            // Notify all lawyers if linked to case
            if ($request->case_id) {
                $case = Cases::find($request->case_id);
                
                $lawyers = User::whereHas('role', function($q) {
                    $q->where('name', 'lawyer');
                })->where('status', 'active')->get();
                
                foreach ($lawyers as $lawyer) {
                    if ($lawyer->id != $user->id) {
                        Notification::create([
                            'user_id' => $lawyer->id,
                            'notifiable_type' => Hearing::class,
                            'notifiable_id' => $hearing->id,
                            'type' => 'hearing_scheduled',
                            'title' => 'New Hearing Scheduled',
                            'message' => "New {$hearing->type} scheduled for case {$case->case_code}: {$hearing->title}",
                            'data' => [
                                'hearing_id' => $hearing->id,
                                'hearing_title' => $hearing->title,
                                'hearing_date' => $hearing->hearing_date,
                                'hearing_type' => $hearing->type,
                                'case_code' => $case->case_code,
                                'case_id' => $case->id
                            ],
                            'action_url' => '/calendar'
                        ]);
                    }
                }
            }

            // Notify assigned user
            if ($request->assigned_to && $request->assigned_to != $user->id) {
                $assignedUser = User::find($request->assigned_to);
                if ($assignedUser) {
                    Notification::create([
                        'user_id' => $request->assigned_to,
                        'notifiable_type' => Hearing::class,
                        'notifiable_id' => $hearing->id,
                        'type' => 'hearing_assigned',
                        'title' => 'Hearing Assigned to You',
                        'message' => "You have been assigned a new {$hearing->type}: {$hearing->title}",
                        'data' => [
                            'hearing_id' => $hearing->id,
                            'hearing_title' => $hearing->title,
                            'hearing_date' => $hearing->hearing_date,
                            'hearing_type' => $hearing->type
                        ],
                        'action_url' => '/calendar'
                    ]);
                }
            }

            // Log activity if linked to case
            if ($hearing->case_id) {
                CaseActivityLog::create([
                    'case_id' => $hearing->case_id,
                    'user_id' => $user->id,
                    'action' => 'scheduled_hearing',
                    'details' => json_encode([
                        'message' => "Scheduled {$hearing->type}: {$hearing->title}",
                        'hearing_id' => $hearing->id,
                        'hearing_date' => $hearing->hearing_date
                    ])
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Hearing created successfully',
                'data' => $hearing
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create hearing',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $hearing = Hearing::findOrFail($id);
            $user = auth()->user();

            $hearingDate = Carbon::parse($hearing->hearing_date);
            if ($hearingDate->isPast()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update past hearings'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'hearing_date' => 'sometimes|required|date|after_or_equal:today',
                'start_time' => 'nullable|date_format:H:i',
                'location' => 'nullable|string|max:255',
                'court_id' => 'nullable|exists:courts,id',
                'type' => 'sometimes|required|in:hearing,meeting,deadline,task,personal,other',
                'status' => 'sometimes|required|in:scheduled,completed,cancelled,rescheduled',
                'assigned_to' => 'nullable|exists:users,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $oldStatus = $hearing->status;
            $oldAssignedTo = $hearing->assigned_to;
            
            $hearing->update($request->only([
                'title', 'description', 'hearing_date', 'start_time',
                'location', 'court_id', 'type', 'status', 'assigned_to'
            ]));

            if ($request->has('status') && $request->status === 'cancelled' && $oldStatus !== 'cancelled') {
                $this->notifyUsers($hearing, 'cancelled', 'Hearing Cancelled', "Hearing '{$hearing->title}' has been cancelled");
            }

            if ($request->assigned_to && $request->assigned_to != $oldAssignedTo) {
                $assignedUser = User::find($request->assigned_to);
                if ($assignedUser) {
                    Notification::create([
                        'user_id' => $request->assigned_to,
                        'notifiable_type' => Hearing::class,
                        'notifiable_id' => $hearing->id,
                        'type' => 'hearing_assigned',
                        'title' => 'Hearing Assigned to You',
                        'message' => "You have been assigned a {$hearing->type}: {$hearing->title}",
                        'data' => [
                            'hearing_id' => $hearing->id,
                            'hearing_title' => $hearing->title,
                            'hearing_date' => $hearing->hearing_date
                        ],
                        'action_url' => '/calendar'
                    ]);
                }
            }

            $hearing->load(['case', 'assignedTo', 'court', 'creator']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Hearing updated successfully',
                'data' => $hearing
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update hearing',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * NEW METHOD: Update hearing status only
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:scheduled,completed,cancelled,rescheduled'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $hearing = Hearing::findOrFail($id);
            $oldStatus = $hearing->status;
            
            $hearing->update(['status' => $request->status]);
            $hearing->load(['case', 'assignedTo', 'court', 'creator']);

            // Notify relevant users about status change
            if ($request->status === 'completed') {
                $this->notifyUsers($hearing, 'completed', 'Hearing Completed', 
                    "Hearing '{$hearing->title}' has been marked as completed");
            }

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'data' => $hearing
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function reschedule(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'new_date' => 'required|date|after_or_equal:today',
            'new_time' => 'nullable|date_format:H:i',
            'reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $hearing = Hearing::findOrFail($id);
            $user = auth()->user();

            if (Carbon::parse($hearing->hearing_date)->isPast()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot reschedule past hearings'
                ], 403);
            }

            $newHearing = $hearing->replicate();
            $newHearing->fill([
                'hearing_date' => $request->new_date,
                'start_time' => $request->new_time,
                'rescheduled_from_id' => $hearing->id,
                'reschedule_reason' => $request->reason,
                'status' => 'scheduled',
                'created_by' => $user->id,
                'reminder_sent' => false,
                'reminder_sent_at' => null
            ]);
            $newHearing->save();

            $hearing->update(['status' => 'rescheduled']);

            $newHearing->load(['case', 'assignedTo', 'court', 'creator']);

            $this->notifyUsers($newHearing, 'rescheduled', 'Hearing Rescheduled', 
                "Hearing has been rescheduled to {$newHearing->hearing_date}");

            if ($hearing->case_id) {
                CaseActivityLog::create([
                    'case_id' => $hearing->case_id,
                    'user_id' => $user->id,
                    'action' => 'rescheduled_hearing',
                    'details' => json_encode([
                        'message' => "Rescheduled hearing from {$hearing->hearing_date} to {$newHearing->hearing_date}",
                        'old_hearing_id' => $hearing->id,
                        'new_hearing_id' => $newHearing->id,
                        'reason' => $request->reason
                    ])
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Hearing rescheduled successfully',
                'data' => [
                    'old_hearing' => $hearing,
                    'new_hearing' => $newHearing
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reschedule hearing',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function cancel(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $hearing = Hearing::findOrFail($id);
            $user = auth()->user();

            if (Carbon::parse($hearing->hearing_date)->isPast()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot cancel past hearings'
                ], 403);
            }

            if ($hearing->status === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hearing is already cancelled'
                ], 422);
            }

            $hearing->update([
                'status' => 'cancelled',
                'metadata' => array_merge($hearing->metadata ?? [], [
                    'cancelled_at' => now(),
                    'cancelled_by' => $user->id,
                    'cancellation_reason' => $request->reason
                ])
            ]);

            $hearing->load(['case', 'assignedTo', 'court', 'creator']);

            $this->notifyUsers($hearing, 'cancelled', 'Hearing Cancelled', 
                "Hearing '{$hearing->title}' has been cancelled. Reason: {$request->reason}");

            if ($hearing->case_id) {
                CaseActivityLog::create([
                    'case_id' => $hearing->case_id,
                    'user_id' => $user->id,
                    'action' => 'cancelled_hearing',
                    'details' => json_encode([
                        'message' => "Cancelled hearing: {$hearing->title}",
                        'hearing_id' => $hearing->id,
                        'reason' => $request->reason
                    ])
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Hearing cancelled successfully',
                'data' => $hearing
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel hearing',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $hearing = Hearing::findOrFail($id);
            $hearing->delete();

            return response()->json([
                'success' => true,
                'message' => 'Hearing deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete hearing',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getStats()
    {
        try {
            $user = auth()->user();
            $role = strtolower($user->role?->name ?? $user->role ?? '');
            
            $query = Hearing::query();
            
            if ($role === 'lawyer') {
                $query->where(function($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhere('assigned_to', $user->id)
                      ->orWhereHas('case', function($caseQuery) use ($user) {
                          $caseQuery->where('assigned_lawyer_id', $user->id);
                      });
                });
            } elseif ($role === 'clerk') {
                $query->where(function($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhere('assigned_to', $user->id)
                      ->orWhereHas('case', function($caseQuery) use ($user) {
                          $caseQuery->where('assigned_clerk_id', $user->id);
                      });
                });
            }

            $today = now()->startOfDay();
            $tomorrow = now()->addDay()->startOfDay();
            $startOfWeek = now()->startOfWeek();
            $endOfWeek = now()->endOfWeek();

            $stats = [
                'today' => (clone $query)->whereDate('hearing_date', $today)->count(),
                'tomorrow' => (clone $query)->whereDate('hearing_date', $tomorrow)->count(),
                'this_week' => (clone $query)->whereBetween('hearing_date', [$startOfWeek, $endOfWeek])->count(),
                'this_month' => (clone $query)->whereMonth('hearing_date', now()->month)->whereYear('hearing_date', now()->year)->count(),
                'upcoming' => (clone $query)->where('hearing_date', '>=', $today)->where('status', 'scheduled')->count(),
                'past' => (clone $query)->where('hearing_date', '<', $today)->count(),
                'by_type' => [
                    'hearing' => (clone $query)->where('type', 'hearing')->count(),
                    'meeting' => (clone $query)->where('type', 'meeting')->count(),
                    'deadline' => (clone $query)->where('type', 'deadline')->count(),
                    'task' => (clone $query)->where('type', 'task')->count(),
                    'personal' => (clone $query)->where('type', 'personal')->count(),
                    'other' => (clone $query)->where('type', 'other')->count()
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function notifyUsers($hearing, $action, $title, $message)
    {
        $usersToNotify = [];
        
        if ($hearing->assigned_to) {
            $usersToNotify[] = $hearing->assigned_to;
        }
        
        if ($hearing->case_id) {
            $case = Cases::find($hearing->case_id);
            if ($case) {
                if ($case->assigned_lawyer_id) {
                    $usersToNotify[] = $case->assigned_lawyer_id;
                }
                if ($case->assigned_clerk_id) {
                    $usersToNotify[] = $case->assigned_clerk_id;
                }
            }
        }
        
        if ($hearing->created_by && !in_array($hearing->created_by, $usersToNotify)) {
            $usersToNotify[] = $hearing->created_by;
        }
        
        $usersToNotify = array_unique(array_filter($usersToNotify));
        
        foreach ($usersToNotify as $userId) {
            if ($userId != auth()->id()) {
                Notification::create([
                    'user_id' => $userId,
                    'notifiable_type' => Hearing::class,
                    'notifiable_id' => $hearing->id,
                    'type' => "hearing_{$action}",
                    'title' => $title,
                    'message' => $message,
                    'data' => [
                        'hearing_id' => $hearing->id,
                        'hearing_title' => $hearing->title,
                        'hearing_date' => $hearing->hearing_date,
                        'hearing_type' => $hearing->type,
                        'case_code' => $hearing->case?->case_code,
                        'case_id' => $hearing->case_id
                    ],
                    'action_url' => '/calendar'
                ]);
            }
        }
    }
}