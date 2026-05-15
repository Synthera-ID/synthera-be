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
        'tier',
        'max_courses',
        'api_daily_limit',
        'api_rate_limit',
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
            'is_active' => 'boolean',
            'price' => 'decimal:2',
        ];
    }

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
