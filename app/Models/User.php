<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /**
     * The table associated with the model.
     */
    protected $table = 'users';

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'userid';

    /**
     * Indicates if the model's ID is auto-incrementing.
     */
    public $incrementing = true;

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'login',
        'password',
        'userdroitid',
        'societeid',
        'agencebid',
        'employeeid',
        'clientid',
        'siteid',
        'plafonremise',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the password for authentication.
     * Laravel's auth system looks for getAuthPassword().
     */
    public function getAuthPassword(): string
    {
        return $this->password;
    }

    /**
     * Get the unique identifier for the user (for session).
     */
    public function getAuthIdentifierName(): string
    {
        return 'userid';
    }

    /**
     * Get the column name for the "remember me" token.
     * Our table doesn't have this column, so we disable it.
     */
    public function getRememberTokenName(): string
    {
        return '';
    }

    /**
     * Relationship: User belongs to a Site.
     */
    public function site()
    {
        return $this->belongsTo(\App\Models\Site::class, 'siteid', 'siteid');
    }

    /**
     * Relationship: User belongs to an Employee.
     */
    public function employee()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'employeeid', 'employeeid');
    }

    /**
     * Relationship: User has a UserDroit (permissions profile).
     */
    public function userdroit()
    {
        return $this->belongsTo(\App\Models\UserDroit::class, 'userdroitid', 'userdroitid');
    }
}
