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
        'details' => 'array', // Automatically converts JSON string to array
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
     * Get the message from details (if available)
     */
    public function getMessageAttribute()
    {
        return $this->details['message'] ?? null;
    }

    /**
     * Get formatted details for display (backward compatibility)
     */
   public function getFormattedDetailsAttribute()
{
    if (!$this->details) {
        return null;
    }

    $details = $this->details;
    
    // Use message field if it exists
    if (isset($details['message'])) {
        return $details['message'];
    }
    
    // Format based on action
    switch ($this->action) {
        case 'created_case':
            return "New case: {$details['case_no']} - {$details['title']}";
        
        case 'updated_case':
            if (isset($details['changes'])) {
                return implode('; ', $details['changes']);
            }
            return "Case updated";
        
        case 'changed_stage':
            return "Stage: {$details['from']} → {$details['to']}";
        
        case 'deleted_case':
            return "Deleted case: {$details['case_no']}";
        
        case 'archived_case':
            return "Archived case: {$details['case_no']}";
        
        default:
            return "Action: {$this->action}"; // Simple fallback
    }
}
}