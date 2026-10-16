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
        return (string) ($this->password ?? '');
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

    /*
     * Les tables liées (sites, employees, userdroits) appartiennent au logiciel de bureau et n'ont pas
     * de modèle Eloquent : elles sont lues en lecture seule via le query builder.
     * (Les anciennes relations pointaient vers des classes Site / Employee / UserDroit inexistantes.)
     */

    /** Site (magasin) de l'utilisateur. */
    public function siteRecord(): ?object
    {
        return $this->siteid ? \Illuminate\Support\Facades\DB::table('sites')->where('siteid', $this->siteid)->first() : null;
    }

    /** Employé (vendeur) lié au compte. */
    public function employeeRecord(): ?object
    {
        return $this->employeeid ? \Illuminate\Support\Facades\DB::table('employees')->where('employeeid', $this->employeeid)->first() : null;
    }

    /** Rôle (profil de droits) de l'utilisateur. */
    public function roleRecord(): ?object
    {
        return $this->userdroitid ? \Illuminate\Support\Facades\DB::table('userdroits')->where('userdroitid', $this->userdroitid)->first() : null;
    }
}
