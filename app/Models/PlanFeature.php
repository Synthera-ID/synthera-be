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
    ];

    public function subscription()
    {
        return $this->belongsTo(
            SubscriptionPlan::class,
            'plan_id'
        );
    }
}
