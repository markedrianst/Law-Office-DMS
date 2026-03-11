<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    
    protected $primaryKey = 'id';

    protected $fillable = [
        'role_id',
        'full_name',
        'email',
        'password_hash',
        'status',
        'last_login',
        'must_change_password'
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected $casts = [
        'last_login' => 'datetime',
        'must_change_password' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function loginLogs()
    {
        return $this->hasMany(LoginLog::class, 'user_id');
    }

    public function isActive()
    {
        return $this->status === 'active';
    }
}