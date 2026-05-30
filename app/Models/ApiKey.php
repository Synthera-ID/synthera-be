<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    protected $fillable = [
        'user_id',
        'api_key',
        'is_active',
        'last_used_at',
        'name',
    ];
}