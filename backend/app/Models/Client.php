<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $table = 'clients';

    protected $fillable = [
        'full_name',
        'contact_no',
        'email',
        'address'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the cases for this client.
     */
    public function cases()
    {
        return $this->hasMany(Cases::class, 'client_id');
    }

    /**
     * Get the active cases for this client.
     */
    public function activeCases()
    {
        return $this->hasMany(Cases::class, 'client_id')->where('case_status', 'active');
    }

    /**
     * Scope a query to search clients.
     */
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where('full_name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('contact_no', 'like', "%{$search}%");
        }
        return $query;
    }

    /**
     * Get client's full name with format.
     */
    public function getFormattedNameAttribute()
    {
        return $this->full_name;
    }

    /**
     * Get client's contact info summary.
     */
    public function getContactSummaryAttribute()
    {
        $parts = [];
        if ($this->contact_no) $parts[] = $this->contact_no;
        if ($this->email) $parts[] = $this->email;
        return implode(' • ', $parts);
    }
}