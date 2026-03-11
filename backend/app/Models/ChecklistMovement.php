<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecklistMovement extends Model
{
    use HasFactory;

    protected $table = 'checklist_movements';

    protected $fillable = [
        'case_id',
        'checklist_id',
        'recorded_by',
        'type',
        'from_to',
        'date',
        'purpose',
        'handled_by',
        'task_name',
        'approval_status',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'date' => 'date',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the case that owns the movement.
     */
    public function case()
    {
        return $this->belongsTo(Cases::class, 'case_id');
    }

    /**
     * Get the checklist item for this movement.
     */
    public function checklist()
    {
        return $this->belongsTo(CaseChecklist::class, 'checklist_id');
    }

    /**
     * Get the user who recorded the movement.
     */
    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Get the user who approved the movement.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope a query to only include pending approvals.
     */
    public function scopePending($query)
    {
        return $query->where('approval_status', 'PENDING');
    }

    /**
     * Scope a query to only include approved movements.
     */
    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'APPROVED');
    }

    /**
     * Scope a query to only include OUT movements.
     */
    public function scopeOut($query)
    {
        return $query->where('type', 'OUT');
    }

    /**
     * Scope a query to only include IN movements.
     */
    public function scopeIn($query)
    {
        return $query->where('type', 'IN');
    }
}