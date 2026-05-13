<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'course_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon'
    ];

    public function courses()
    {
        return $this->hasMany(Course::class, 'category_id');
    }
}