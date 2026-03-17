<?php
// app/Models/Hearing.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hearing extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hearings';

    protected $fillable = [
        'case_id',
        'title',
        'description',
        'hearing_date',
        'start_time',
        'location',
        'court_id',
        'type',
        'status',
        'created_by',
        'assigned_to',
        'reminder_sent',
        'reminder_sent_at',
        'rescheduled_from_id',
        'reschedule_reason',
        'metadata'
    ];

    protected $casts = [
        'hearing_date' => 'date',
        'start_time' => 'datetime:H:i',
        'reminder_sent' => 'boolean',
        'reminder_sent_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Relationships
    public function case()
    {
        return $this->belongsTo(Cases::class, 'case_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function court()
    {
        return $this->belongsTo(Court::class, 'court_id');
    }

    public function rescheduledFrom()
    {
        return $this->belongsTo(Hearing::class, 'rescheduled_from_id');
    }

    public function rescheduledTo()
    {
        return $this->hasMany(Hearing::class, 'rescheduled_from_id');
    }

    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->where('created_by', $userId)
              ->orWhere('assigned_to', $userId)
              ->orWhereHas('case', function($caseQuery) use ($userId) {
                  $caseQuery->where('assigned_lawyer_id', $userId)
                            ->orWhere('assigned_clerk_id', $userId);
              });
        });
    }

    public function scopeForLawyer($query, $lawyerId)
    {
        return $query->where(function($q) use ($lawyerId) {
            $q->where('assigned_to', $lawyerId)
              ->orWhereHas('case', function($caseQuery) use ($lawyerId) {
                  $caseQuery->where('assigned_lawyer_id', $lawyerId);
              });
        });
    }

    public function scopeForClerk($query, $clerkId)
    {
        return $query->where(function($q) use ($clerkId) {
            $q->where('assigned_to', $clerkId)
              ->orWhereHas('case', function($caseQuery) use ($clerkId) {
                  $caseQuery->where('assigned_clerk_id', $clerkId);
              });
        });
    }

    public function scopeUpcoming($query, $days = 30)
    {
        return $query->where('hearing_date', '>=', now())
                     ->where('hearing_date', '<=', now()->addDays($days))
                     ->where('status', 'scheduled');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('hearing_date', now());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('hearing_date', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('hearing_date', now()->month)
                     ->whereYear('hearing_date', now()->year);
    }

    public function scopePersonal($query, $userId)
    {
        return $query->where('created_by', $userId)
                     ->whereNull('case_id');
    }

    // Accessors
    public function getFormattedStartTimeAttribute()
    {
        return $this->start_time?->format('g:i A');
    }

    public function getFormattedDateAttribute()
    {
        return $this->hearing_date?->format('M d, Y');
    }

    public function getIsTodayAttribute()
    {
        return $this->hearing_date?->isToday();
    }

    public function getIsTomorrowAttribute()
    {
        return $this->hearing_date?->isTomorrow();
    }

    public function getIsAllDayAttribute()
    {
        return is_null($this->start_time);
    }

    public function getColorAttribute()
    {
        $colors = [
            'hearing' => '#1a4972',
            'meeting' => '#10b981',
            'deadline' => '#ef4444',
            'task' => '#f59e0b',
            'personal' => '#8b5cf6',
            'other' => '#6b7280'
        ];
        
        return $colors[$this->type] ?? '#6b7280';
    }

    public function getIconAttribute()
    {
        $icons = [
            'hearing' => '⚖️',
            'meeting' => '🤝',
            'deadline' => '⏰',
            'task' => '✅',
            'personal' => '📌',
            'other' => '📅'
        ];
        
        return $icons[$this->type] ?? '📅';
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'scheduled' => 'bg-blue-100 text-blue-700',
            'completed' => 'bg-emerald-100 text-emerald-700',
            'cancelled' => 'bg-red-100 text-red-700',
            'rescheduled' => 'bg-amber-100 text-amber-700'
        ];
        
        return $badges[$this->status] ?? 'bg-slate-100 text-slate-700';
    }
}