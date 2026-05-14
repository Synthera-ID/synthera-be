<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | GET ALL COURSE + FILTER
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = Course::with('category');

        // Filter title
        if ($request->title) {
            $query->where(
                'title',
                'like',
                '%' . $request->title . '%'
            );
        }

        // Filter category
        if ($request->category_id) {
            $query->where(
                'category_id',
                $request->category_id
            );
        }

        $courses = $query->get();

        return CourseResource::collection($courses);
    }

    /*
    |--------------------------------------------------------------------------
    | GET DETAIL COURSE
    |--------------------------------------------------------------------------
    */

    public function show($id)
    {
        $course = Course::with('category')->findOrFail($id);

        return new CourseResource($course);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE COURSE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        try {

            $request->validate([
                'title' => 'required|string|max:255',
                'slug' => 'required|unique:courses,slug',
                'description' => 'required',
                'price' => 'required|numeric',
                'category_id' => 'required|exists:course_categories,id',
                'min_tier' => 'required',
            ]);

            $course = Course::create([
                'title' => $request->title,
                'slug' => $request->slug,
                'description' => $request->description,
                'price' => $request->price,
                'category_id' => $request->category_id,
                'min_tier' => $request->min_tier,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Course created successfully',
                'data' => new CourseResource($course)
            ], 201);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);

        }
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE COURSE
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|unique:courses,slug,' . $id,
            'description' => 'required',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:course_categories,id',
            'min_tier' => 'required',
        ]);

        $course->update([
            'title' => $request->title,
            'slug' => $request->slug,
            'description' => $request->description,
            'price' => $request->price,
            'category_id' => $request->category_id,
            'min_tier' => $request->min_tier,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Course updated successfully',
            'data' => new CourseResource($course)
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE COURSE
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        $course = Course::findOrFail($id);

        $course->delete();

        return response()->json([
            'success' => true,
            'message' => 'Course deleted successfully'
        ]);
    }
}