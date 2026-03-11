<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseActivityLog extends Model
{
    use HasFactory;

    protected $table = 'case_activity_logs';

    protected $fillable = [
        'case_id',
        'user_id',
        'action',
        'details'
    ];

    protected $casts = [
        'details' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the case that owns the log.
     */
    public function case()
    {
        return $this->belongsTo(Cases::class, 'case_id');
    }

    /**
     * Get the user who performed the action.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope a query to only include logs for a specific case.
     */
    public function scopeForCase($query, $caseId)
    {
        return $query->where('case_id', $caseId);
    }

    /**
     * Scope a query to only include logs by a specific user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include logs of a specific action.
     */
    public function scopeOfAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Get formatted details for display.
     */
    public function getFormattedDetailsAttribute()
    {
        if (!$this->details) {
            return null;
        }

        $details = $this->details;
        
        switch ($this->action) {
            case 'created_case':
                return "Created case: {$details['title']} ({$details['case_no']})";
            
            case 'updated_case':
                $fields = implode(', ', $details['fields'] ?? []);
                return "Updated case fields: {$fields}";
            
            case 'changed_stage':
                return "Changed stage from {$details['from']} to {$details['to']}";
            
            case 'assigned_lawyer':
                return "Assigned lawyer: {$details['lawyer']}";
            
            case 'assigned_clerk':
                return "Assigned clerk: {$details['clerk']}";
            
            case 'deleted_case':
                return "Deleted case: {$details['title']} ({$details['case_code']})";
            
            case 'archived_case':
                return "Archived case";
            
            default:
                return json_encode($details);
        }
    }
}