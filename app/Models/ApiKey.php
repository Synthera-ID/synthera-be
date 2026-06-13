<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// This model represents an API key that is associated with a user. Each API key has a unique string value that can be used for authentication when accessing protected API endpoints. The model includes fields for the user ID, the API key itself, whether the key is active, the last time it was used, and a name for the key to help users identify it. This allows users to manage their API keys effectively, including generating new keys, deactivating old ones, and tracking their usage.
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