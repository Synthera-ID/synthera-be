<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'duration_days',
        'tier'
    ];
    public function features()
    {
        return $this->hasMany(
            PlanFeature::class,
            'plan_id'
        );
    }
    public function memberships()
    {
        return $this->hasMany(
            Membership::class,
            'plan_id'
        );
    }
}
