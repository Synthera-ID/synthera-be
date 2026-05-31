<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlanFeature;
use Illuminate\Http\Request;

class PlanFeatureController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | ADMIN: List all plan features with search/filter/pagination
    |--------------------------------------------------------------------------
    */
    public function adminIndex(Request $request)
    {
        $query = PlanFeature::with('subscription')->where('IsDeleted', 0);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('feature_key', 'like', "%{$search}%")
                  ->orWhere('feature_label', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($planId = $request->query('plan_id')) {
            $query->where('plan_id', $planId);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->query('is_active') === 'true' ? 1 : 0);
        }

        $perPage = $request->query('per_page', 50);
        $features = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $features->items(),
            'meta' => [
                'current_page' => $features->currentPage(),
                'last_page' => $features->lastPage(),
                'per_page' => $features->perPage(),
                'total' => $features->total(),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN: Create plan feature
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'feature_key' => 'required|string|max:100',
            'feature_label' => 'required|string|max:255',
            'limit_value' => 'nullable|integer',
            'is_unlimited' => 'boolean',
            'description' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $feature = PlanFeature::create(array_merge($validated, [
            'is_unlimited' => $validated['is_unlimited'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
            'CreatedBy' => $request->user()->name ?? 'Synthera',
            'CreatedDate' => now(),
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Plan feature created successfully.',
            'data' => $feature->load('subscription'),
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN: Update plan feature
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        $feature = PlanFeature::where('IsDeleted', 0)->findOrFail($id);

        $validated = $request->validate([
            'plan_id' => 'sometimes|exists:subscription_plans,id',
            'feature_key' => 'sometimes|string|max:100',
            'feature_label' => 'sometimes|string|max:255',
            'limit_value' => 'nullable|integer',
            'is_unlimited' => 'sometimes|boolean',
            'description' => 'sometimes|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $feature->update(array_merge($validated, [
            'LastUpdateBy' => $request->user()->name ?? 'Synthera',
            'LastUpdateDate' => now(),
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Plan feature updated successfully.',
            'data' => $feature->fresh()->load('subscription'),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN: Delete plan feature (soft delete)
    |--------------------------------------------------------------------------
    */
    public function destroy(Request $request, $id)
    {
        $feature = PlanFeature::where('IsDeleted', 0)->findOrFail($id);

        $feature->update([
            'IsDeleted' => 1,
            'LastUpdateBy' => $request->user()->name ?? 'Synthera',
            'LastUpdateDate' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Plan feature deleted successfully.',
        ]);
    }
}
