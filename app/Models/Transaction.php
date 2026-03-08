<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'invoice_code',
        'amount',
        'discount_amount',
        'final_amount',
        'status'
        ];
}