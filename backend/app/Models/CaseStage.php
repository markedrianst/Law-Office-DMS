<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseStage extends Model
{
    use HasFactory;

    protected $table = 'case_stages';

    protected $fillable = [
        'name',
        'color',
        'order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the cases in this stage.
     */
    public function cases()
    {
        return $this->hasMany(Cases::class, 'current_stage_id');
    }

    /**
     * Get the stage histories where this is the target stage.
     */
    public function targetHistories()
    {
        return $this->hasMany(CaseStageHistory::class, 'to_stage_id');
    }

    /**
     * Get the stage histories where this is the source stage.
     */
    public function sourceHistories()
    {
        return $this->hasMany(CaseStageHistory::class, 'from_stage_id');
    }

    /**
     * Scope a query to only include active stages.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order by the order field.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Get the first active stage (for default assignment).
     */
    public static function getFirstActiveId()
    {
        return self::where('is_active', true)
            ->orderBy('order')
            ->value('id');
    }

    /**
     * Get all stages formatted for dropdown.
     */
    public static function getForDropdown()
    {
        return self::active()
            ->ordered()
            ->get(['id', 'name', 'color']);
    }
}