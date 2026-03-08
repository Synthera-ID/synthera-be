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
        'gateway_ref',
        'amount',
        'status',
        'paid_at'
        ];
}