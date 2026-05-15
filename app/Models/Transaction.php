<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    protected $fillable = [
        'invoice_code',
        'user_id',
        'payment_id',
        'plan_id',
        'discount_id',
        'amount',
        'discount_amount',
        'final_amount',
        'transaction_status',
        'notes',
        'CompanyCode',
        'Status',
        'IsDeleted',
        'CreatedBy',
        'CreatedDate',
        'LastUpdateBy',
        'LastUpdateDate',
    ];
}
