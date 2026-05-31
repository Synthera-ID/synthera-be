<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | PUBLIC: GET ALL COURSE + FILTER
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $query = Course::with('category');

        if ($request->title) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        $courses = $query->get();

        return CourseResource::collection($courses);
    }

    /*
    |--------------------------------------------------------------------------
    | PUBLIC: GET DETAIL COURSE
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $course = Course::with('category')->findOrFail($id);
        return new CourseResource($course);
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN: List all courses with search/filter/pagination
    |--------------------------------------------------------------------------
    */
    public function adminIndex(Request $request)
    {
        $query = Course::with('category');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($tier = $request->query('min_tier')) {
            $query->where('min_tier', $tier);
        }

        if ($request->has('is_published')) {
            $query->where('is_published', $request->query('is_published') === 'true' ? 1 : 0);
        }

        $perPage = $request->query('per_page', 20);
        $courses = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $courses->items(),
            'meta' => [
                'current_page' => $courses->currentPage(),
                'last_page' => $courses->lastPage(),
                'per_page' => $courses->perPage(),
                'total' => $courses->total(),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN: CREATE COURSE
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|unique:courses,slug',
            'description' => 'required',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:course_categories,id',
            'min_tier' => 'required',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'content_url' => 'nullable|string',
            'video_url' => 'nullable|string',
            'tag' => 'nullable|array',
            'is_published' => 'boolean',
        ]);

        $thumbnailUrl = null;
        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('thumbnails', 'public');
            $thumbnailUrl = '/storage/' . $path;
        }

        $course = Course::create(array_merge($validated, [
            'thumbnail_url' => $thumbnailUrl ?? ($validated['thumbnail_url'] ?? null),
            'is_published' => $validated['is_published'] ?? false,
            'CreatedBy' => $request->user()->name ?? 'Synthera',
            'CreatedDate' => now(),
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Course created successfully.',
            'data' => new CourseResource($course->load('category'))
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN: UPDATE COURSE
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'slug' => 'sometimes|unique:courses,slug,' . $id,
            'description' => 'sometimes|string',
            'category_id' => 'sometimes|exists:course_categories,id',
            'min_tier' => 'sometimes|string',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'content_url' => 'nullable|string',
            'video_url' => 'nullable|string',
            'tag' => 'nullable|array',
            'is_published' => 'sometimes|boolean',
        ]);
        if ($request->hasFile('thumbnail')) {

            if ($course->thumbnail_url) {
                $oldPath = public_path($course->thumbnail_url);

                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $file = $request->file('thumbnail');

            $filename = time() . '_' . $file->getClientOriginalName();

            $file->move(public_path('thumbnails'), $filename);

            $validated['thumbnail_url'] = '/thumbnails/' . $filename;
        }

        // Remove thumbnail key from validated (it's the file, not the url)
        unset($validated['thumbnail']);

        $course->update(array_merge($validated, [
            'LastUpdateBy' => $request->user()->name ?? 'Synthera',
            'LastUpdateDate' => now(),
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Course updated successfully.',
            'data' => new CourseResource($course->fresh()->load('category'))
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN: DELETE COURSE
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        $course->delete();

        return response()->json([
            'success' => true,
            'message' => 'Course deleted successfully.'
        ]);
    }
}
