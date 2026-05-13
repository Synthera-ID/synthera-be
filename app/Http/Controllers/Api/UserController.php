<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Get user detail
     */
    public function show($id)
    {
        $user = User::with('membership')->findOrFail($id);

        return new UserResource($user);
    }

    /**
     * Update user
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'avatar_url' => 'nullable|string',
            'company_code' => 'nullable|string|max:100',
            'role' => 'nullable|string',
            'two_factor_enabled' => 'nullable|boolean',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'User berhasil diupdate',
            'data' => new UserResource(
                $user->load('membership')
            ),
        ]);
    }
}