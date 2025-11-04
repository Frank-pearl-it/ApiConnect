<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'autotask_id',
        'name',
        'domain',
        'primary_email',
        'uses_microsoft_login'
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'idCompany');
    }
}
