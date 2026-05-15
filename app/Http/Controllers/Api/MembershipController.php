<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MembershipController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | PUBLIC: GET ALL
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        return Membership::with(['user', 'subscription'])->get();
    }

    /*
    |--------------------------------------------------------------------------
    | PUBLIC: GET DETAIL
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        return Membership::with(['user', 'subscription'])->findOrFail($id);
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN: List all memberships with search/filter/pagination
    |--------------------------------------------------------------------------
    */
    public function adminIndex(Request $request)
    {
        $query = Membership::with(['user', 'subscription']);

        // Search by user name or email
        if ($search = $request->query('search')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($status = $request->query('status')) {
            $query->where('membership_status', $status);
        }

        // Filter by plan
        if ($planId = $request->query('plan_id')) {
            $query->where('plan_id', $planId);
        }

        $perPage = $request->query('per_page', 20);
        $memberships = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $memberships->items(),
            'meta' => [
                'current_page' => $memberships->currentPage(),
                'last_page' => $memberships->lastPage(),
                'per_page' => $memberships->perPage(),
                'total' => $memberships->total(),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN: CREATE
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'plan_id' => 'required|exists:subscription_plans,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'membership_status' => ['sometimes', Rule::in(['active', 'expired', 'cancelled'])],
                'auto_renew' => 'sometimes|boolean',
            ]);

            $membership = Membership::create([
                'user_id' => $validated['user_id'],
                'plan_id' => $validated['plan_id'],
                'membership_status' => $validated['membership_status'] ?? 'active',
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'auto_renew' => $validated['auto_renew'] ?? false,
                'CreatedBy' => $request->user()->name ?? 'Synthera',
                'CreatedDate' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Membership created successfully.',
                'data' => $membership->load(['user', 'subscription'])
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat membership.',
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN: UPDATE (including upgrade/downgrade plan)
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        $membership = Membership::findOrFail($id);

        $validated = $request->validate([
            'plan_id' => 'sometimes|exists:subscription_plans,id',
            'membership_status' => ['sometimes', Rule::in(['active', 'expired', 'cancelled'])],
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date',
            'auto_renew' => 'sometimes|boolean',
        ]);

        // If plan_id changes → upgrade/downgrade
        if (isset($validated['plan_id']) && $validated['plan_id'] != $membership->plan_id) {
            $newPlan = SubscriptionPlan::findOrFail($validated['plan_id']);
            // Auto-adjust end_date based on new plan duration
            if (!isset($validated['end_date'])) {
                $startDate = isset($validated['start_date'])
                    ? $validated['start_date']
                    : $membership->start_date;
                $validated['end_date'] = \Carbon\Carbon::parse($startDate)
                    ->addDays($newPlan->duration_days)
                    ->toDateString();
            }
        }

        $membership->update(array_merge($validated, [
            'LastUpdateBy' => $request->user()->name ?? 'Synthera',
            'LastUpdateDate' => now(),
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Membership updated successfully.',
            'data' => $membership->fresh()->load(['user', 'subscription'])
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN: DELETE
    |--------------------------------------------------------------------------
    */
    public function destroy(Request $request, $id)
    {
        $membership = Membership::findOrFail($id);

        $membership->delete();

        return response()->json([
            'success' => true,
            'message' => 'Membership deleted successfully.'
        ]);
    }
}