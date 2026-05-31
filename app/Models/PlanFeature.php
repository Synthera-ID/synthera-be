<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanFeature extends Model
{
    protected $fillable = [
        'plan_id',
        'feature_key',
        'feature_label',
        'limit_value',
        'is_unlimited',
        'description',
        'is_active',
        'CompanyCode',
        'Status',
        'IsDeleted',
        'CreatedBy',
        'CreatedDate',
        'LastUpdateBy',
        'LastUpdateDate',
    ];

    protected function casts(): array
    {
        return [
            'is_unlimited' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function subscription()
    {
        return $this->belongsTo(
            SubscriptionPlan::class,
            'plan_id'
        );
    }
}
