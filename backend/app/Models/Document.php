<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $table = 'documents';

    protected $fillable = [
        'type',
        'color',
        'category',
        'requires_approval',
        'approval_status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'requires_approval' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the document categories.
     */
    public static function getCategories()
    {
        return ['Pleading', 'Letter', 'Evidence', 'Court Issuance', 'Other'];
    }

    /**
     * Get the approval requests for this document.
     */
    public function approvalRequests()
    {
        return $this->hasMany(DocumentApproval::class, 'document_id');
    }

    /**
     * Get the latest approval request.
     */
    public function latestApproval()
    {
        return $this->hasOne(DocumentApproval::class, 'document_id')->latest();
    }

    /**
     * Get the user who approved this document.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope a query to only include active documents.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order by sort_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('type');
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeOfCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to filter by approval status.
     */
    public function scopeOfApprovalStatus($query, $status)
    {
        return $query->where('approval_status', $status);
    }

    /**
     * Scope a query to only include documents that require approval.
     */
    public function scopeRequiresApproval($query)
    {
        return $query->where('requires_approval', true);
    }

    /**
     * Scope pending approvals.
     */
    public function scopePendingApproval($query)
    {
        return $query->where('requires_approval', true)
                     ->where('approval_status', 'pending');
    }

    /**
     * Scope approved documents.
     */
    public function scopeApproved($query)
    {
        return $query->where(function($q) {
            $q->where('requires_approval', false)
              ->orWhere('approval_status', 'approved');
        });
    }
}