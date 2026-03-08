<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;

class CourseController extends Controller
{
    public function index()
    {
        return Course::with('category')->get();
    }

    public function show($id)
    {
        return Course::with('category')->findOrFail($id);
    }
}