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
        'thumbnail_url',
        'video_url',
        'tag',
        'content_url',
        'duration'
    ];


    protected $casts = [
        "tag" => 'array'
    ];


    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
