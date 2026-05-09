<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Models\Course;

class CourseController extends Controller
{
    public function index()
    {
        $getCourse = Course::with('category')->get();
        return  CourseResource::collection($getCourse);
    }

    public function show($id)
    {
        return Course::with('category')->findOrFail($id);
    }
}
