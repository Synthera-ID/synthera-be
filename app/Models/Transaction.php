<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected static function booted()
    {
        static::saved(function ($transaction) {
            app(\App\Services\InvoiceService::class)->generateInvoiceFromTransaction($transaction);
        });
    }

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

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    protected $fillable = [
        'invoice_code',
        'user_id',
        'payment_string',
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
