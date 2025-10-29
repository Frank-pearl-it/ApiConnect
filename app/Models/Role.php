<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Collection;

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
