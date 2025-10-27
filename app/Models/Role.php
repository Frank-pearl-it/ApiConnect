<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name',
        'idCompany',
        'roleOrder',
        'permissions',
        'readTicketsOfRoles',
        'getNotificationsOf',
    ];

    protected $casts = [
        'permissions' => 'array',
        'readTicketsOfRoles' => 'array',
        'getNotificationsOf' => 'array',
    ];
 
}
