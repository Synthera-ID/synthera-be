<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiUsageLog extends Model
{
    protected $fillable = [
        'user_id',
        'api_key_id',
        'endpoint',
        'method',
        'status_code',
        'ip_address',
        'called_at',
    ];
}
