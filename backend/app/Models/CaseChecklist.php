<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseChecklist extends Model
{
    use HasFactory;

    protected $table = 'case_checklists';

    protected $fillable = [
        'case_id',
        'created_by',
        'task',
        'status',
        'due_date',
        'assigned_clerk_id',
        'assigned_to',
        'notes',
        'is_out',
        'completed_at'
    ];

    protected $casts = [
        'due_date' => 'date',
        'is_out' => 'boolean',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the case that owns the checklist.
     */
    public function case()
    {
        return $this->belongsTo(Cases::class, 'case_id');
    }

    /**
     * Get the user who created the checklist.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the clerk assigned to this checklist.
     */
    public function assignedClerk()
    {
        return $this->belongsTo(User::class, 'assigned_clerk_id');
    }

    /**
     * Get the movements for this checklist.
     */
    public function movements()
    {
        return $this->hasMany(ChecklistMovement::class, 'checklist_id');
    }

    /**
     * Scope a query to only include todo items.
     */
    public function scopeTodo($query)
    {
        return $query->where('status', 'todo');
    }

    /**
     * Scope a query to only include in-progress items.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in-progress');
    }

    /**
     * Scope a query to only include done items.
     */
    public function scopeDone($query)
    {
        return $query->where('status', 'done');
    }

    /**
     * Scope a query to only include out items.
     */
    public function scopeOut($query)
    {
        return $query->where('is_out', true);
    }

    /**
     * Scope a query to only include items for a specific case.
     */
    public function scopeForCase($query, $caseId)
    {
        return $query->where('case_id', $caseId);
    }
}