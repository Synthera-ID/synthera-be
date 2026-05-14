<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $table = 'courses';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'price',
        'category_id',
        'min_tier'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}