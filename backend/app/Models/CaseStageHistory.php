<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseStageHistory extends Model
{
    use HasFactory;

    protected $table = 'case_stage_histories';

    protected $fillable = [
        'case_id',
        'from_stage_id',
        'to_stage_id',
        'changed_by',
        'remarks'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the case that owns the history.
     */
    public function case()
    {
        return $this->belongsTo(Cases::class, 'case_id');
    }

    /**
     * Get the from stage.
     */
    public function fromStage()
    {
        return $this->belongsTo(CaseStage::class, 'from_stage_id');
    }

    /**
     * Get the to stage.
     */
    public function toStage()
    {
        return $this->belongsTo(CaseStage::class, 'to_stage_id');
    }

    /**
     * Get the user who changed the stage.
     */
    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Scope a query to only include history for a specific case.
     */
    public function scopeForCase($query, $caseId)
    {
        return $query->where('case_id', $caseId);
    }

    /**
     * Scope a query to only include history changed by a specific user.
     */
    public function scopeChangedBy($query, $userId)
    {
        return $query->where('changed_by', $userId);
    }

    /**
     * Get formatted history description.
     */
    public function getDescriptionAttribute()
    {
        $from = $this->fromStage?->name ?? 'Start';
        $to = $this->toStage?->name ?? 'Unknown';
        $by = $this->changedBy?->full_name ?? 'System';
        
        return "{$from} → {$to} by {$by}";
    }
}