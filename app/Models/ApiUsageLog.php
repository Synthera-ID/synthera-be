<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiUsageLog extends Model
{
    protected $fillable = [
        'user_id',
        'membership_id',
        'endpoint',
        'method',
        'status_code',
        'ip_address',
        'called_at',
    ];
}