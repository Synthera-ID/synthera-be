<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    // GET ALL
    public function index()
    {
        return Membership::all();
    }

    // GET DETAIL
    public function show($id)
    {
        return Membership::findOrFail($id);
    }

    // CREATE
    public function store(Request $request)
{
    try {

        $request->validate([
            'user_id' => 'required',
            'plan_id' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
        ]);

        $membership = Membership::create([
            'user_id' => $request->user_id,
            'plan_id' => $request->plan_id,
            'membership_status' => 'active',
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'auto_renew' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Membership created successfully',
            'data' => $membership
        ], 201);

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);

    }
}

    // UPDATE
    public function update(Request $request, $id)
    {
        $membership = Membership::findOrFail($id);

        $membership->update([
            'name' => $request->name,
            'price' => $request->price
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Membership updated successfully',
            'data' => $membership
        ]);
    }

    // DELETE
    public function destroy($id)
    {
        $membership = Membership::findOrFail($id);

        $membership->delete();

        return response()->json([
            'success' => true,
            'message' => 'Membership deleted successfully'
        ]);
    }
}