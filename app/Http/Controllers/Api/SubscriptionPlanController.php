<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        return response()->json(SubscriptionPlan::all());
    }

    public function show($id)
    {
        return response()->json(SubscriptionPlan::findOrFail($id));
    }
    


public function store(request $request)
{
  $validated = $request->validate([
    'name' => 'required|string|max:255',
    'description' => 'nullable|string',
    'price' => 'required|numeric',
    'duration_months' => 'required|integer',
  ]);

    $subscription = SubscriptionPlan::create($validated);

    return response()->json 
    ([
        'message' => 'Subscription plan berhasil dibuat.',
        'data' => $subscription
    ], 201);

}

public function update(Request $request, $id)
{
    $subscription = SubscriptionPlan::findOrFail($id);

    $validated = $request->validate([
        'name' => 'sometimes|string|max:255',
        'description' => 'nullable|string',
        'price' => 'sometimes|numeric',
        'duration_months' => 'sometimes|integer',
    ]);

    $subscription->update($validated);

    return response()->json([
        'message' => 'Subscription plan berhasil diperbarui.',
        'data' => $subscription
    ]);
}

public function destroy($id)
{
    $subscription = SubscriptionPlan::findOrFail($id);
    $subscription->delete();

    return response()->json([
        'message' => 'Subscription plan berhasil dihapus.'
    ]);
}
}
