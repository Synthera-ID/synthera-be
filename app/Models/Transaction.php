<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
    'invoice_code',
    'user_id',
    'plan_id',
    'discount_id',
    'amount',
    'discount_amount',
    'final_amount',
    'transaction_status',

    'CompanyCode',
    'Status',
    'IsDeleted',
    'CreatedBy',
    'CreatedDate',
];
}