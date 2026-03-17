<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    use HasFactory;

    protected $table = 'login_logs';
    
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'details',
        'email_attempted',
        'ip_address',
        'user_agent',
        'status',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}