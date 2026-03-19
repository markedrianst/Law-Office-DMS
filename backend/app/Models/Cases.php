<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cases extends Model
{
    use HasFactory;

    protected $table = 'cases';

    protected $fillable = [
        'case_no',
        'case_code',
        'title',
        'category_id',
        'client_id',
        'court_or_office',
        'docket_no',
        'assigned_lawyer_id',
        'assigned_clerk_id',
        'priority',
        'case_status',
        'current_stage_id',
        'summary',
        'is_out',
        'created_by'
    ];

    protected $casts = [
        'is_out' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the category that owns the case.
     */
    public function category()
    {
        return $this->belongsTo(CaseCategory::class, 'category_id');
    }

    /**
     * Get the client that owns the case.
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * Get the lawyer assigned to the case.
     */
    public function lawyer()
    {
        return $this->belongsTo(User::class, 'assigned_lawyer_id');
    }

    /**
     * Get the clerk assigned to the case.
     */
    public function clerk()
    {
        return $this->belongsTo(User::class, 'assigned_clerk_id');
    }

    /**
     * Get the current stage of the case.
     */
    public function currentStage()
    {
        return $this->belongsTo(CaseStage::class, 'current_stage_id');
    }

    /**
     * Get the user who created the case.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the checklists for the case.
     */
    public function checklists()
    {
        return $this->hasMany(CaseChecklist::class, 'case_id');
    }

    /**
     * Get the folder movements for the case.
     */
    public function folderMovements()
    {
        return $this->hasMany(FolderMovement::class, 'case_id');
    }

    /**
     * Get the checklist movements for the case.
     */
    public function checklistMovements()
    {
        return $this->hasMany(ChecklistMovement::class, 'case_id');
    }

    /**
     * Get the stage histories for the case.
     */
    public function stageHistories()
    {
        return $this->hasMany(CaseStageHistory::class, 'case_id');
    }

    /**
     * Get the activity logs for the case.
     */
    public function activityLogs()
    {
        return $this->hasMany(CaseActivityLog::class, 'case_id');
    }

    /**
     * Scope a query to only include active cases.
     */
    public function scopeActive($query)
    {
        return $query->where('case_status', 'active');
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeOfStatus($query, $status)
    {
        if ($status) {
            return $query->where('case_status', $status);
        }
        return $query;
    }

    /**
     * Scope a query to filter by priority.
     */
    public function scopeOfPriority($query, $priority)
    {
        if ($priority) {
            return $query->where('priority', $priority);
        }
        return $query;
    }

    /**
     * Scope a query to filter by stage.
     */
    public function scopeOfStage($query, $stageId)
    {
        if ($stageId) {
            return $query->where('current_stage_id', $stageId);
        }
        return $query;
    }

    public function clients()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * Scope a query to search by case code, title, or client name.
     */
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function($q) use ($search) {
                $q->where('case_code', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhereHas('client', function($clientQuery) use ($search) {
                      $clientQuery->where('full_name', 'like', "%{$search}%");
                  });
            });
        }
        return $query;
    }
}