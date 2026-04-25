<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $fillable = [
    'code',
    'description',
    'discount_type',
    'discount_value',
    'min_purchase',
    'max_uses',
    'valid_from',
    'valid_until',
    'is_active',

    'CompanyCode',
    'Status',
    'IsDeleted',
    'CreatedBy',
    'CreatedDate',
];
}