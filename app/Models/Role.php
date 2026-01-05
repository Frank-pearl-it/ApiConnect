<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Collection;

class Role extends Model
{
    protected $fillable = [
        'name',
        'description',
        'idCompany',
        'roleOrder',
        'permissions',
        'readTicketsOfRoles', 
    ];

    protected $casts = [
        'permissions' => 'array',
        'readTicketsOfRoles' => 'array', 
    ];


    public function users()
    {
        return $this->hasMany(User::class, 'idRole');
    }

}
