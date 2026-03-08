<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'category_id',
        'duration'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}