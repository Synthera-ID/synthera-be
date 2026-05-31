<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionResource;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SubscriptionPlanController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | PUBLIC: List all active plans (for user subscription page)
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $getSubsc = SubscriptionPlan::with("features")->get();
        return SubscriptionResource::collection($getSubsc);
    }

    public function show($id)
    {
        return response()->json(SubscriptionPlan::with('features')->findOrFail($id));
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN: List all plans with search/filter/pagination
    |--------------------------------------------------------------------------
    */
    public function adminIndex(Request $request)
    {
        $query = SubscriptionPlan::with('features');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('tier', 'like', "%{$search}%");
            });
        }

        if ($tier = $request->query('tier')) {
            $query->where('tier', $tier);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->query('is_active') === 'true' ? 1 : 0);
        }

        $perPage = $request->query('per_page', 50);
        $plans = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $plans->items(),
            'meta' => [
                'current_page' => $plans->currentPage(),
                'last_page' => $plans->lastPage(),
                'per_page' => $plans->perPage(),
                'total' => $plans->total(),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN: Create plan
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'duration_days' => 'required|integer',
            'tier' => ['required', Rule::in(['basic', 'pro', 'exclusive'])],
            'max_courses' => 'nullable|integer',
            'api_daily_limit' => 'nullable|integer',
            'api_rate_limit' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $subscription = SubscriptionPlan::create(array_merge($validated, [
            'CreatedBy' => $request->user()->name ?? 'Synthera',
            'CreatedDate' => now(),
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Subscription plan berhasil dibuat.',
            'data' => $subscription->load('features')
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN: Update plan
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        $subscription = SubscriptionPlan::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric',
            'duration_days' => 'sometimes|integer',
            'tier' => ['sometimes', Rule::in(['basic', 'pro', 'exclusive'])],
            'max_courses' => 'nullable|integer',
            'api_daily_limit' => 'nullable|integer',
            'api_rate_limit' => 'nullable|integer',
            'is_active' => 'sometimes|boolean',
        ]);

        $subscription->update(array_merge($validated, [
            'LastUpdateBy' => $request->user()->name ?? 'Synthera',
            'LastUpdateDate' => now(),
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Subscription plan berhasil diperbarui.',
            'data' => $subscription->fresh()->load('features')
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN: Delete plan
    |--------------------------------------------------------------------------
    */
    public function destroy(Request $request, $id)
    {
        $subscription = SubscriptionPlan::findOrFail($id);
        $subscription->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subscription plan berhasil dihapus.'
        ]);
    }
}
