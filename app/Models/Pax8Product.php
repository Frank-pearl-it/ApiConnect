<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pax8Product extends Model
{
    protected $primaryKey = 'pax8_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'pax8_id',
        'name',
        'vendorName',
        'shortDescription',
        'sku',
        'vendorSku',
        'altVendorSku',
        'requiresCommitment',
        'raw',
    ];

    protected $casts = [
        'raw' => 'array',
        'requiresCommitment' => 'boolean',
    ];
}
