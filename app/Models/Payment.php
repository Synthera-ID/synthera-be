<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
   protected $fillable = [
    'transaction_id',
    'user_id',
    'payment_method',
    'payment_gateway',
    'payment_status',
    'gateway_ref',
    'amount',
    'status',
    'paid_at',
    'CompanyCode',
    'Status',
    'IsDeleted',
    'CreatedBy',
    'CreatedDate',
    'LastUpdateBy',
    'LastUpdateDate'
];
}